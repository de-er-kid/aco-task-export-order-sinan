import React, { useState, useEffect } from 'react';
import { DateRange } from 'react-date-range';
import { format, addDays, subDays, subMonths } from 'date-fns';
import 'react-date-range/dist/styles.css';
import 'react-date-range/dist/theme/default.css';

const DateRangePicker = ({ startDate, endDate, onDateChange }) => {
    const [isOpen, setIsOpen] = useState(false);
    const [dateRange, setDateRange] = useState([
        {
            startDate: startDate ? new Date(startDate) : new Date(),
            endDate: endDate ? new Date(endDate) : new Date(),
            key: 'selection'
        }
    ]);

    useEffect(() => {
        if (startDate || endDate) {
            setDateRange([
                {
                    startDate: startDate ? new Date(startDate) : new Date(),
                    endDate: endDate ? new Date(endDate) : new Date(),
                    key: 'selection'
                }
            ]);
        }
    }, [startDate, endDate]);

    const handleRangeChange = (ranges) => {
        const { selection } = ranges;
        setDateRange([selection]);
        
        onDateChange('start_date', format(selection.startDate, 'yyyy-MM-dd'));
        onDateChange('end_date', format(selection.endDate, 'yyyy-MM-dd'));
    };

    const handlePresetClick = (startDateValue, endDateValue) => {
        const newRange = {
            startDate: startDateValue,
            endDate: endDateValue,
            key: 'selection'
        };
        
        setDateRange([newRange]);
        onDateChange('start_date', format(startDateValue, 'yyyy-MM-dd'));
        onDateChange('end_date', format(endDateValue, 'yyyy-MM-dd'));
    };

    const toggleCalendar = () => {
        setIsOpen(!isOpen);
    };

    return (
        <div className="aco-date-range-picker">
            <h3>Select Date Range</h3>
            
            <div className="aco-date-display" onClick={toggleCalendar}>
                <div className="aco-date-display-input">
                    <div className="aco-date-display-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div className="aco-date-display-text">
                        {startDate && endDate ? (
                            startDate === endDate ? 
                                format(new Date(startDate), 'MMM dd, yyyy') : 
                                `${format(new Date(startDate), 'MMM dd, yyyy')} - ${format(new Date(endDate), 'MMM dd, yyyy')}`
                        ) : (
                            'Select date range'
                        )}
                    </div>
                    <div className="aco-date-display-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
            
            {isOpen && (
                <div className="aco-date-range-calendar-container">
                    <div className="aco-date-range-presets">
                        <button 
                            type="button" 
                            className="aco-date-preset-btn"
                            onClick={() => handlePresetClick(subDays(new Date(), 7), new Date())}
                        >
                            Last 7 Days
                        </button>
                        <button 
                            type="button" 
                            className="aco-date-preset-btn"
                            onClick={() => handlePresetClick(subDays(new Date(), 30), new Date())}
                        >
                            Last 30 Days
                        </button>
                        <button 
                            type="button" 
                            className="aco-date-preset-btn"
                            onClick={() => handlePresetClick(subMonths(new Date(), 3), new Date())}
                        >
                            Last 3 Months
                        </button>
                        <button 
                            type="button" 
                            className="aco-date-preset-btn"
                            onClick={() => handlePresetClick(new Date(), new Date())}
                        >
                            Today
                        </button>
                    </div>
                    
                    <DateRange
                        editableDateInputs={true}
                        onChange={handleRangeChange}
                        moveRangeOnFirstSelection={false}
                        ranges={dateRange}
                        months={2}
                        direction="horizontal"
                        className="aco-date-range-calendar"
                        maxDate={new Date()}
                    />
                    
                    <div className="aco-date-range-actions">
                        <button 
                            type="button" 
                            className="aco-date-range-apply-btn"
                            onClick={toggleCalendar}
                        >
                            Apply
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DateRangePicker;