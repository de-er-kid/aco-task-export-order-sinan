import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('aco-export-orders-app');
    if (container) {
        ReactDOM.render(<App />, container);
    }
});