import React, { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { store } from "./redux/store";
import { Provider } from 'react-redux';
import App from "./react/controllers/App.jsx";
import './app.css';

const rootElement = document.getElementById('root');
const root = createRoot(rootElement);
root.render(
    <StrictMode>
        <Provider store={store}>
            <App />
        </Provider>
    </StrictMode>
);
