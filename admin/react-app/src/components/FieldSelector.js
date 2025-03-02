import React, { useState } from 'react';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

// Remove any MUI imports

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
            const currentFields = selectedFields.filter(f => !fieldKeys.includes(f));
            onChange([...currentFields, ...fieldKeys]);
        }
    };

    const handleDragEnd = (result) => {
        if (!result.destination) return;

        // Create a new array with the selected fields in their current order
        const orderedSelectedFields = [...selectedFields];
        
        // Find the dragged item
        const [reorderedItem] = orderedSelectedFields.splice(result.source.index, 1);
        
        // Insert it at the new position
        orderedSelectedFields.splice(result.destination.index, 0, reorderedItem);
        
        // Update the parent component with the new order
        onChange(orderedSelectedFields);
    };

    const renderFieldList = (category) => {
        const categoryFields = fields[category] || {};
        
        // Create an array of selected fields from this category in their current order
        const selectedFromCategory = selectedFields.filter(field => 
            Object.keys(categoryFields).includes(field)
        );
        
        // Create an array of unselected fields from this category
        const unselectedFromCategory = Object.keys(categoryFields).filter(
            field => !selectedFields.includes(field)
        );

        if (Object.keys(categoryFields).length === 0) {
            return <div className="aco-empty-fields">No fields available in this category</div>;
        }

        return (
            <div className="aco-field-list-container">
                <div className="aco-select-all-wrapper">
                    <label className="aco-select-all-label">
                        <input
                            type="checkbox"
                            checked={Object.keys(categoryFields).every(field => selectedFields.includes(field))}
                            onChange={() => handleSelectAll(category)}
                            className="aco-select-all-checkbox"
                        />
                        <span>Select All</span>
                    </label>
                </div>

                <h4 className="aco-section-title">Selected Fields (Drag to reorder)</h4>
                <DragDropContext onDragEnd={handleDragEnd}>
                    <Droppable droppableId={`selected-fields-${category}`}>
                        {(provided) => (
                            <div
                                {...provided.droppableProps}
                                ref={provided.innerRef}
                                className="aco-draggable-list"
                            >
                                {selectedFromCategory.length > 0 ? (
                                    selectedFromCategory.map((key, index) => (
                                        <Draggable key={key} draggableId={key} index={index}>
                                            {(provided, snapshot) => (
                                                <div
                                                    ref={provided.innerRef}
                                                    {...provided.draggableProps}
                                                    className={`aco-draggable-item selected ${snapshot.isDragging ? 'dragging' : ''}`}
                                                >
                                                    <div {...provided.dragHandleProps} className="aco-drag-handle">
                                                        ≡
                                                    </div>
                                                    <label className="aco-field-label">
                                                        <input
                                                            type="checkbox"
                                                            checked={true}
                                                            onChange={() => handleFieldToggle(key)}
                                                            className="aco-field-checkbox"
                                                        />
                                                        <span>{categoryFields[key]}</span>
                                                    </label>
                                                </div>
                                            )}
                                        </Draggable>
                                    ))
                                ) : (
                                    <div className="aco-empty-selection">No fields selected</div>
                                )}
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </DragDropContext>

                <h4 className="aco-section-title">Available Fields</h4>
                <div className="aco-available-fields">
                    {unselectedFromCategory.map(key => (
                        <div key={key} className="aco-draggable-item">
                            <label className="aco-field-label">
                                <input
                                    type="checkbox"
                                    checked={false}
                                    onChange={() => handleFieldToggle(key)}
                                    className="aco-field-checkbox"
                                />
                                <span>{categoryFields[key]}</span>
                            </label>
                        </div>
                    ))}
                </div>
            </div>
        );
    };
    
    return (
        <div className="aco-field-selector">
            <h3>Select Fields to Export</h3>
            
            <div className="aco-tabs">
                <button
                    className={`aco-tab ${activeTab === 'standard' ? 'active' : ''}`}
                    onClick={() => setActiveTab('standard')}
                >
                    Standard Fields
                </button>
                <button
                    className={`aco-tab ${activeTab === 'addons' ? 'active' : ''}`}
                    onClick={() => setActiveTab('addons')}
                >
                    Custom Addon Fields
                </button>
            </div>
            
            <div className="aco-tab-content">
                {activeTab === 'standard' && renderFieldList('standard')}
                {activeTab === 'addons' && renderFieldList('addons')}
            </div>
            
            <div className="aco-selected-count">
                Selected: {selectedFields.length} fields
            </div>
        </div>
    );
};

export default FieldSelector;
