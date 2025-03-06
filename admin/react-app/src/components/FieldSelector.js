import React, { useState, useEffect } from 'react';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
// Import the new dnd-kit libraries at the top of your file
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors, DragOverlay } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, useSortable, horizontalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

const FieldSelector = ({ fields, selectedFields, onChange }) => {
    const [activeTab, setActiveTab] = useState('standard');
    const [allFields, setAllFields] = useState({});
    // Add state to track the active (currently dragged) item
    const [activeId, setActiveId] = useState(null);

    // Combine all fields for better tracking
    useEffect(() => {
        const combined = {
            ...fields.standard,
            ...fields.addons
        };
        setAllFields(combined);
    }, [fields]);

    const handleFieldToggle = (field) => {
        if (selectedFields.includes(field)) {
            onChange(selectedFields.filter(f => f !== field));
        } else {
            onChange([...selectedFields, field]);
        }
    };

    const handleSelectAll = (category) => {
        const fieldKeys = Object.keys(fields[category] || {});
        if (fieldKeys.every(field => selectedFields.includes(field))) {
            onChange(selectedFields.filter(f => !fieldKeys.includes(f)));
        } else {
            const currentFields = selectedFields.filter(f => !fieldKeys.includes(f));
            onChange([...currentFields, ...fieldKeys]);
        }
    };

    const handleDragEnd = (result) => {
        // Drop outside the list
        if (!result.destination) return;
        
        // No position change
        if (result.destination.index === result.source.index) return;

        const items = Array.from(selectedFields);
        const [reorderedItem] = items.splice(result.source.index, 1);
        items.splice(result.destination.index, 0, reorderedItem);
        
        onChange(items);
    };

    const getFieldLabel = (key, categoryFields) => {
        // Handle both string values and object values
        const fieldValue = categoryFields[key];
        return typeof fieldValue === 'string' ? fieldValue : fieldValue?.label || key;
    };

    // Replace the renderFieldList function with this updated version
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
    
        // Set up sensors with improved activation constraints
        const sensors = useSensors(
            useSensor(PointerSensor, {
                // Make dragging more responsive with lower activation threshold
                activationConstraint: {
                    distance: 3, // Reduced from 5
                    tolerance: 3, // Reduced from 5
                    delay: 0,
                },
            }),
            useSensor(KeyboardSensor, {
                coordinateGetter: sortableKeyboardCoordinates,
            })
        );
    
        // Add handlers for drag start and drag end
        const handleDragStart = (event) => {
            const { active } = event;
            setActiveId(active.id);
            
            // Add a class to the body to indicate dragging is in progress
            document.body.classList.add('aco-dragging-in-progress');
        };
        
        const handleDragEndNew = (event) => {
            const { active, over } = event;
            
            // Reset active ID
            setActiveId(null);
            
            // Remove the dragging class
            document.body.classList.remove('aco-dragging-in-progress');
            
            // Only proceed if we have both active and over items
            if (!active || !over) return;
            
            if (active.id !== over.id) {
                const oldIndex = selectedFromCategory.indexOf(active.id);
                const newIndex = selectedFromCategory.indexOf(over.id);
                
                // Validate indices to prevent errors
                if (oldIndex === -1 || newIndex === -1) return;
                
                // Create a copy of the selected fields
                const newSelectedFields = [...selectedFields];
                
                // Get the reordered fields for this category
                const reorderedCategoryFields = arrayMove(selectedFromCategory, oldIndex, newIndex);
                
                // Remove all selected fields from this category
                const fieldsWithoutCategory = newSelectedFields.filter(field => 
                    !Object.keys(categoryFields).includes(field)
                );
                
                // Add back the reordered fields
                const updatedFields = [...fieldsWithoutCategory, ...reorderedCategoryFields];
                
                // Use onChange to update the parent component
                onChange(updatedFields);
                
                // Add a class to the item for animation
                setTimeout(() => {
                    const items = document.querySelectorAll('.aco-draggable-item');
                    items.forEach(item => {
                        if (item.getAttribute('data-id') === active.id) {
                            item.classList.add('reordered');
                            setTimeout(() => {
                                item.classList.remove('reordered');
                            }, 300);
                        }
                    });
                }, 0);
            }
        };
    
        // Enhanced SortableItem component with improved visual feedback
        const SortableItem = ({ id }) => {
            const {
                attributes,
                listeners,
                setNodeRef,
                transform,
                transition,
                isDragging
            } = useSortable({ 
                id,
                transition: {
                    duration: 150,
                    easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                }
            });
            
            const style = {
                transform: CSS.Transform.toString(transform),
                transition,
                zIndex: isDragging ? 999 : 1,
                opacity: isDragging ? 0.3 : 1, // Make the original item more transparent when dragging
                position: 'relative',
            };
            
            return (
                <div
                    ref={setNodeRef}
                    style={style}
                    data-id={id}
                    className={`aco-draggable-item selected ${isDragging ? 'dragging' : ''}`}
                >
                    <div {...attributes} {...listeners} className="aco-drag-handle">
                        ≡
                    </div>
                    <label className="aco-field-label">
                        <input
                            type="checkbox"
                            checked={true}
                            onChange={() => handleFieldToggle(id)}
                            className="aco-field-checkbox"
                        />
                        <span title={getFieldLabel(id, categoryFields)}>
                            {getFieldLabel(id, categoryFields)}
                        </span>
                    </label>
                </div>
            );
        };
        
        // Create a component for the drag overlay (the visual element that follows the cursor)
        const DragOverlayItem = ({ id }) => {
            if (!id) return null;
            
            return (
                <div 
                    className="aco-draggable-item selected dragging-overlay"
                    style={{
                        width: 'calc(33.333% - 16px)',
                        maxWidth: '250px'
                    }}
                >
                    <div className="aco-drag-handle">≡</div>
                    <label className="aco-field-label">
                        <input
                            type="checkbox"
                            checked={true}
                            readOnly
                            className="aco-field-checkbox"
                        />
                        <span title={getFieldLabel(id, categoryFields)}>
                            {getFieldLabel(id, categoryFields)}
                        </span>
                    </label>
                </div>
            );
        };
    
        return (
            <div className="aco-field-list-container">
                <div className="aco-select-all-wrapper">
                    <label className="aco-select-all-label">
                        <input
                            type="checkbox"
                            checked={Object.keys(categoryFields).length > 0 && 
                                   Object.keys(categoryFields).every(field => selectedFields.includes(field))}
                            onChange={() => handleSelectAll(category)}
                            className="aco-select-all-checkbox"
                        />
                        <span>Select All</span>
                    </label>
                </div>
    
                <h4 className="aco-section-title">Selected Fields (Drag to reorder)</h4>
                
                <DndContext 
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    onDragStart={handleDragStart}
                    onDragEnd={handleDragEndNew}
                >
                    <div className="aco-draggable-list">
                        {selectedFromCategory.length > 0 ? (
                            <SortableContext 
                                items={selectedFromCategory}
                                strategy={horizontalListSortingStrategy}
                            >
                                {selectedFromCategory.map((key) => (
                                    <SortableItem key={key} id={key} />
                                ))}
                            </SortableContext>
                        ) : (
                            <div className="aco-empty-selection">No fields selected</div>
                        )}
                    </div>
                    
                    {/* Add the DragOverlay component to show a visual representation during drag */}
                    <DragOverlay adjustScale={true} zIndex={1000}>
                        {activeId ? <DragOverlayItem id={activeId} /> : null}
                    </DragOverlay>
                </DndContext>
    
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
                                <span title={getFieldLabel(key, categoryFields)}>
                                    {getFieldLabel(key, categoryFields)}
                                </span>
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
                    type="button"
                >
                    Standard Fields
                </button>
                <button
                    className={`aco-tab ${activeTab === 'addons' ? 'active' : ''}`}
                    onClick={() => setActiveTab('addons')}
                    type="button"
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
