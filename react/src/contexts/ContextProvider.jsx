import {createContext, useContext, useState} from "react";

const StateContext = createContext({
    currentUser: {},
    token: null,
    surveys: [],
    questionTypes: [],
    setCurrentUser: () => {
    },
    setToken: () => {
    },
    setSurveys: () => {
    }
})

const tmpSurveys = [];

export const ContextProvider = ({children}) => {
    const [currentUser, setCurrentUser] = useState({});
    const [token, _setToken] = useState(localStorage.getItem('TOKEN') || '');
    const [surveys, setSurveys] = useState(tmpSurveys);
    const [questionTypes] = useState(['text', 'select', 'radio', 'checkbox', 'textarea'])

    const setToken = (token) => {
        if(token) {
            localStorage.setItem('TOKEN', token);
        }else{
            localStorage.removeItem('TOKEN')
        }

        _setToken(token);
    }

    return (
        <StateContext.Provider value={{
            currentUser,
            setCurrentUser,
            token,
            setToken,
            surveys,
            questionTypes
        }}>
            {children}
        </StateContext.Provider>
    )
}


export const useStateContext = () => useContext(StateContext)
