import React from 'react';

const DateRangePicker = ({ startDate, endDate, onDateChange }) => {
    return (
        <div className="aco-date-range-picker">
            <h3>Select Date Range</h3>
            <div className="aco-date-inputs">
                <div className="aco-date-field">
                    <label htmlFor="start_date">Start Date:</label>
                    <input
                        type="date"
                        id="start_date"
                        value={startDate}
                        onChange={(e) => onDateChange('start_date', e.target.value)}
                        required
                    />
                </div>
                <div className="aco-date-field">
                    <label htmlFor="end_date">End Date:</label>
                    <input
                        type="date"
                        id="end_date"
                        value={endDate}
                        onChange={(e) => onDateChange('end_date', e.target.value)}
                        required
                        min={startDate}
                    />
                </div>
            </div>
        </div>
    );
};

export default DateRangePicker;