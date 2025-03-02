import React, { useState, useEffect } from 'react';
import DateRangePicker from './components/DateRangePicker';
import FieldSelector from './components/FieldSelector';
import FormatSelector from './components/FormatSelector';

const App = () => {
    const [fields, setFields] = useState({
        standard: {},
        addons: {}
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [exportStatus, setExportStatus] = useState({
        processing: false,
        success: false,
        error: null,
        fileUrl: null
    });
    
    const [formData, setFormData] = useState({
        start_date: '',
        end_date: '',
        fields: [],
        format: 'csv'
    });

    useEffect(() => {
        fetchAvailableFields();
    }, []);

    const fetchAvailableFields = async () => {
        try {
            setLoading(true);
            const response = await fetch(`${acoExportOrdersSettings.root}aco-export/v1/fields`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': acoExportOrdersSettings.nonce
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch fields');
            }

            const data = await response.json();
            setFields(data);
            
            // Set default fields
            const defaultFields = Object.keys(data.standard).slice(0, 5);
            setFormData(prev => ({
                ...prev,
                fields: defaultFields
            }));
            
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleDateChange = (type, date) => {
        setFormData(prev => ({
            ...prev,
            [type]: date
        }));
    };

    const handleFieldChange = (selectedFields) => {
        setFormData(prev => ({
            ...prev,
            fields: selectedFields
        }));
    };

    const handleFormatChange = (format) => {
        setFormData(prev => ({
            ...prev,
            format
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        // Validate form
        if (!formData.start_date || !formData.end_date) {
            setExportStatus({
                processing: false,
                success: false,
                error: 'Please select a date range'
            });
            return;
        }

        if (formData.fields.length === 0) {
            setExportStatus({
                processing: false,
                success: false,
                error: 'Please select at least one field to export'
            });
            return;
        }

        try {
            setExportStatus({
                processing: true,
                success: false,
                error: null,
                fileUrl: null
            });

            const response = await fetch(`${acoExportOrdersSettings.root}aco-export/v1/export`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': acoExportOrdersSettings.nonce
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Export failed');
            }

            const data = await response.json();
            
            setExportStatus({
                processing: false,
                success: true,
                error: null,
                fileUrl: data.file_url
            });
            
        } catch (err) {
            setExportStatus({
                processing: false,
                success: false,
                error: err.message,
                fileUrl: null
            });
        }
    };

    if (loading) {
        return <div className="aco-loading">Loading...</div>;
    }

    if (error) {
        return <div className="aco-error">Error: {error}</div>;
    }

    return (
        <div className="aco-export-orders-container">
            <form onSubmit={handleSubmit} className="aco-export-form">
                <div className="aco-form-row">
                    <DateRangePicker 
                        startDate={formData.start_date}
                        endDate={formData.end_date}
                        onDateChange={handleDateChange}
                    />
                </div>
                
                <div className="aco-form-row">
                    <FieldSelector 
                        fields={fields}
                        selectedFields={formData.fields}
                        onChange={handleFieldChange}
                    />
                </div>
                
                <div className="aco-form-row">
                    <FormatSelector 
                        format={formData.format}
                        onChange={handleFormatChange}
                    />
                </div>
                
                <div className="aco-form-row">
                    <button 
                        type="submit" 
                        className="button button-primary"
                        disabled={exportStatus.processing}
                    >
                        {exportStatus.processing ? 'Processing...' : 'Export Orders'}
                    </button>
                </div>
                
                {exportStatus.error && (
                    <div className="aco-export-error">
                        Error: {exportStatus.error}
                    </div>
                )}
                
                {exportStatus.success && (
                    <div className="aco-export-success">
                        <p>Export completed successfully!</p>
                        <a 
                            href={exportStatus.fileUrl} 
                            className="button"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Download File
                        </a>
                    </div>
                )}
            </form>
        </div>
    );
};

export default App;