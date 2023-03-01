import {createContext, useContext, useState} from "react";

const StateContext = createContext({
    currentUser: {},
    token: null,
    surveys: [],
    setCurrentUser: () => {},
    setToken: () => {},
    setSurveys: () => {}
})

const tmpSurveys = [

];

export const ContextProvider = ({children}) => {
    const [currentUser, setCurrentUser] = useState({
        name: 'Tom Cook',
        email: 'tom@example.com',
        imageUrl:
            'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    });
    const [token, setToken] = useState('1234');
    const [surveys, setSurveys] = useState(tmpSurveys);
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
