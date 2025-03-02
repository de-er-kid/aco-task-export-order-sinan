import React, { useState } from 'react';

const FieldSelector = ({ fields, selectedFields, onChange }) => {
    const [activeTab, setActiveTab] = useState('standard');
    
    const handleFieldToggle = (field) => {
        if (selectedFields.includes(field)) {
            onChange(selectedFields.filter(f => f !== field));
        } else {
            onChange([...selectedFields, field]);
        }
    };

    const handleSelectAll = (category) => {
        const fieldKeys = Object.keys(fields[category]);
        if (fieldKeys.every(field => selectedFields.includes(field))) {
            onChange(selectedFields.filter(f => !fieldKeys.includes(f)));
        } else {
            onChange([...new Set([...selectedFields, ...fieldKeys])]);
        }
    };

    const isAllSelected = (category) => {
        const fieldKeys = Object.keys(fields[category]);
        return fieldKeys.length > 0 && fieldKeys.every(field => selectedFields.includes(field));
    };
    
    return (
        <div className="aco-field-selector">
            <h3>Select Fields to Export</h3>
            
            <div className="aco-tabs">
                <button 
                    type="button"
                    className={`aco-tab ${activeTab === 'standard' ? 'active' : ''}`}
                    onClick={() => setActiveTab('standard')}
                >
                    Standard Fields
                </button>
                <button 
                    type="button"
                    className={`aco-tab ${activeTab === 'addons' ? 'active' : ''}`}
                    onClick={() => setActiveTab('addons')}
                >
                    Custom Addon Fields
                </button>
            </div>
            
            <div className="aco-field-list">
                {activeTab === 'standard' ? (
                    Object.keys(fields.standard).length > 0 ? (
                        <>
                            <div className="aco-field-item">
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={isAllSelected('standard')}
                                        onChange={() => handleSelectAll('standard')}
                                    />
                                    Select All
                                </label>
                            </div>
                            {Object.entries(fields.standard).map(([key, label]) => (
                                <div key={key} className="aco-field-item">
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={selectedFields.includes(key)}
                                            onChange={() => handleFieldToggle(key)}
                                        />
                                        {label}
                                    </label>
                                </div>
                            ))}
                        </>
                    ) : (
                        <p>No standard fields available</p>
                    )
                ) : (
                    Object.keys(fields.addons).length > 0 ? (
                        <>
                            <div className="aco-field-item">
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={isAllSelected('addons')}
                                        onChange={() => handleSelectAll('addons')}
                                    />
                                    Select All
                                </label>
                            </div>
                            {Object.entries(fields.addons).map(([key, label]) => (
                                <div key={key} className="aco-field-item">
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={selectedFields.includes(key)}
                                            onChange={() => handleFieldToggle(key)}
                                        />
                                        {label}
                                    </label>
                                </div>
                            ))}
                        </>
                    ) : (
                        <p>No custom addon fields found</p>
                    )
                )}
            </div>
            
            <div className="aco-selected-count">
                Selected: {selectedFields.length} fields
            </div>
        </div>
    );
};

export default FieldSelector;
