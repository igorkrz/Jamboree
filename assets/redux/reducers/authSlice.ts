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
    setIsAuthenticated: (state, action: PayloadAction<boolean>) => ({
      ...state,
      isAuthenticated: action.payload,
    }),
    setLoginChecked: (state, action: PayloadAction<boolean>) => ({
      ...state,
      isLoginChecked: action.payload,
    }),
    setToken: (state, action: PayloadAction<boolean>) => ({
      ...state,
      token: action.payload,
    }),
    setUser: (state, action: PayloadAction<string>) => ({
      ...state,
      user: action.payload,
    }),
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
    await useAxios.get(LOGOUT);
    dispatch(setIsAuthenticated(false));
    dispatch(setUser(null));
    dispatch(setToken(null));
  } catch (err: any) {
      console.log(err)
  }
};

export const checkLogin = () => async (dispatch) => {
  try {
    const response = await useAxios.get(CHECK_LOGIN);
    dispatch(setIsAuthenticated(response.data));
  } catch (err: any) {
    dispatch(setIsAuthenticated(false));
  } finally {
    dispatch(setLoginChecked(true));
  }
};

export default authSlice.reducer;
