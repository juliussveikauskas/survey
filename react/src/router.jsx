import {createBrowserRouter, Navigate} from "react-router-dom";
import Dashboard from "./views/Dashboard.jsx";
import Surveys from "./views/Surveys.jsx";
import Login from "./views/Login.jsx";
import Signup from "./views/Signup.jsx";
import GuestLayout from "./components/GuestLayout.jsx";
import DefaultLayout from "./components/DefaultLayout.jsx";
import SurveyView from "./views/SurveyView.jsx";

const router = createBrowserRouter([
    {
        path: '/',
        element: <DefaultLayout/>,
        children: [
            {
                path: '/',
                element: <Navigate to='/dashboard'/>
            },
            {
                path: '/dashboard',
                element: <Dashboard/>
            },
            {
                path: '/surveys',
                element: <Surveys/>
            },
            {
                path: '/surveys/create',
                element: <SurveyView/>
            },
        ]
    },
    {
        path: '/',
        element: <GuestLayout/>,
        children: [
            {
                path: '/login',
                element: <Login/>
            },
            {
                path: '/signup',
                element: <Signup/>
            }
        ]
    }
])


export default router;
