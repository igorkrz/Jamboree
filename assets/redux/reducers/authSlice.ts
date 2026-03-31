import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { ENDPOINTS } from '../../api/constants/Endpoints';
import useAxios from "../../react/helpers/useAxios.jsx";

const { LOGOUT, CHECK_LOGIN } = ENDPOINTS;

const initialState = {
    isAuthenticated: false,
    isLoginChecked: false,
    token: null,
    user: null,
};

export const authSlice = createSlice({
    name: 'auth',
    initialState,
    reducers: {
        setIsAuthenticated: (state, action: PayloadAction<boolean>) => {
            state.isAuthenticated = action.payload;
        },
        setLoginChecked: (state, action: PayloadAction<boolean>) => {
            state.isLoginChecked = action.payload;
        },
        setToken: (state, action: PayloadAction<string | null>) => {
            state.token = action.payload;
        },
        setUser: (state, action: PayloadAction<any>) => {
            state.user = action.payload;
        },
    },
});

export const {
    setIsAuthenticated,
    setLoginChecked,
    setToken,
    setUser,
} = authSlice.actions;

export const logout = () => async (dispatch) => {
    try {
        await useAxios.post(LOGOUT);
    } catch (err: any) {
        console.log(err)
    } finally {
        sessionStorage.removeItem('access_token');
        dispatch(setIsAuthenticated(false));
        dispatch(setUser(null));
        dispatch(setToken(null));
    }
};

export const checkLogin = () => async (dispatch) => {
    try {
        const response = await useAxios.get(CHECK_LOGIN);
        if (response.data === false) {
            sessionStorage.removeItem('access_token');
            dispatch(setIsAuthenticated(false));
            dispatch(setUser(null));
            dispatch(setToken(null));
        } else {
            dispatch(setIsAuthenticated(true));
            if (response.data !== null) {
                dispatch(setUser(response.data));
            }
        }
    } catch (err: any) {
        sessionStorage.removeItem('access_token');
        dispatch(setIsAuthenticated(false));
        dispatch(setUser(null));
        dispatch(setToken(null));
    } finally {
        dispatch(setLoginChecked(true));
    }
};

export default authSlice.reducer;
