import React from 'react';

const FormatSelector = ({ format, onChange }) => {
    return (
        <div className="aco-format-selector">
            <h3>Export Format</h3>
            <div className="aco-format-options">
                <label>
                    <input
                        type="radio"
                        value="csv"
                        checked={format === 'csv'}
                        onChange={() => onChange('csv')}
                    />
                    CSV
                </label>
                <label>
                    <input
                        type="radio"
                        value="pdf"
                        checked={format === 'pdf'}
                        onChange={() => onChange('pdf')}
                    />
                    PDF
                </label>
            </div>
        </div>
    );
};

export default FormatSelector;