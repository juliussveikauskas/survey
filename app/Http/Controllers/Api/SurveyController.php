<?php

namespace App\Http\Controllers\Api;

use App\Enums\QuestionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyStoreRequest;
use App\Http\Requests\SurveyUpdateRequest;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use \Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $user = $request->user();

        return SurveyResource::collection(Survey::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SurveyStoreRequest $request): SurveyResource
    {
        $data = $request->validated();

        if (isset($data['image'])) {
            $data['image'] = $this->saveImage($data['image']);
        }

        $survey = Survey::create($data);

        foreach ($data['questions'] as $question) {
            $question['survey_id'] = $survey->id;
            $this->createQuestion($question);
        }

        return new SurveyResource($survey);
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey, Request $request): SurveyResource
    {
        $user = $request->user();
        if ($survey->user_id !== $user->id) {
            abort(403, 'Unauthorized action');
        }

        return new SurveyResource($survey);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SurveyUpdateRequest $request, Survey $survey): SurveyResource
    {
        $data = $request->validated();

        if (isset($data['image'])) {
            $data['image'] = $this->saveImage($data['image']);

            if ($survey->image) {
                $absolutePath = public_path($survey->image);
                File::delete($absolutePath);
            }
        }

        $survey->update($data);

        $existingIds = $survey->questions()->pluck('id')->toArray();
        $newIds = Arr::pluck($data['questions'], 'id');

        $toDelete = array_diff($existingIds, $newIds);
        $toAdd = array_diff($newIds, $existingIds);

        SurveyQuestion::destroy($toDelete);

        foreach ($data['questions'] as $question) {
            if (in_array($question['id'], $toAdd)) {
                $question['survey_id'] = $survey->id;
                $this->createQuestion($question);
            }
        }

        $questionMap = collect($data['questions'])->keyBy('id');
        foreach ($survey->questions as $question) {
            if (isset($questionMap[$question->id])) {
                $this->updateQuestion($question, $questionMap[$question->id]);
            }
        }

        return new SurveyResource($survey);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey, Request $request): Response
    {
        $user = $request->user();
        if ($user->id !== $survey->user_id) {
            abort(403, 'Unauthorized action');
        }

        $survey->delete();

        if ($survey->image) {
            File::delete(public_path($survey->image));
        }

        return response('', 204);
    }

    private function saveImage($image)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            $image = substr($image, strpos($image, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }

            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $dir = 'images/';
        $file = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $file;
        if (!file_exists($absolutePath)) {
            mkdir($absolutePath, 0755, true);
        }
        file_put_contents($relativePath, $image);

        return $relativePath;
    }

    private function createQuestion($data)
    {
        if (is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        $validator = Validator::make($data, [
            'question' => 'required|string',
            'type' => ['required', new Enum(QuestionTypeEnum::class)],
            'description' => 'nullable|string',
            'data' => 'present',
            'survey_id' => 'exists:App\Models\Survey,id'
        ]);

        return SurveyQuestion::create($validator->validated());
    }

    private function updateQuestion(SurveyQuestion $question, $data)
    {
        if (is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        $validator = Validator::make($data, [
            'id' => 'exists:App\Models\SurveyQuestion,id',
            'question' => 'required|string',
            'type' => ['required', new Enum(QuestionTypeEnum::class)],
            'description' => 'nullable|string',
            'data' => 'present',
        ]);

        return $question->update($validator->validated());
    }
}
