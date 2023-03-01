import {createContext, useContext, useState} from "react";

const StateContext = createContext({
    currentUser: {},
    token: null,
    surveys: [],
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
            surveys
        }}>
            {children}
        </StateContext.Provider>
    )
}


export const useStateContext = () => useContext(StateContext)
