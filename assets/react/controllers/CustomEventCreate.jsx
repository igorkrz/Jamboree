import React, { useState } from "react";
import useAxios from "../helpers/useAxios.jsx";
import { 
    PhotoIcon, 
    CalendarIcon, 
    MapPinIcon, 
    BanknotesIcon, 
    LinkIcon,
    PencilSquareIcon,
    ArrowLeftIcon,
    PlusIcon
} from "@heroicons/react/24/outline";

export default function CustomEventCreate() {
    const [formData, setFormData] = useState({
        url: "",
        name: "",
        description: "",
        price: "",
        file: null,
        holdingDate: "",
        location: {
            venue: "",
            city: "",
        },
    });

    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [previewUrl, setPreviewUrl] = useState(null);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
        if (errors[name]) {
            setErrors({ ...errors, [name]: null });
        }
    };

    const handleLocationChange = (e) => {
        const { name, value } = e.target;
        setFormData({
            ...formData,
            location: { ...formData.location, [name]: value }
        });
    };

    const handleFileChange = (e) => {
        const selectedFile = e.target.files[0];
        if (selectedFile) {
            setFormData({ ...formData, file: selectedFile });
            setPreviewUrl(URL.createObjectURL(selectedFile));
        }
    };

    const validate = () => {
        let tempErrors = {};
        if (!formData.name) tempErrors.name = "Event name is required";
        if (!formData.holdingDate) tempErrors.holdingDate = "Date is required";
        return tempErrors;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const validationErrors = validate();
        if (Object.keys(validationErrors).length > 0) {
            setErrors(validationErrors);
            return;
        }
        setLoading(true);
        try {
            await useAxios.post("/api/custom_events", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });

            window.location.href = '/custom_events';
        } catch (error) {
            console.error("Error creating event:", error);
            alert("Failed to create event. Please check the form and try again.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
            <div className="max-w-4xl mx-auto">
                {/* Header */}
                <div className="flex items-center justify-between mb-10">
                    <div>
                        <button 
                            onClick={() => window.history.back()}
                            className="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-4"
                        >
                            <ArrowLeftIcon className="h-4 w-4 mr-1" />
                            Back to Events
                        </button>
                        <h1 className="text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                            Create <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Community Event</span>
                        </h1>
                        <p className="mt-2 text-lg text-gray-600 dark:text-gray-400 font-medium">
                            Share your event with the community and reach more people.
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div className="lg:col-span-2 space-y-6">
                            <div className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 transition-all">
                                <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <PencilSquareIcon className="h-5 w-5 mr-2 text-indigo-500" />
                                    General Information
                                </h2>
                                
                                <div className="space-y-6">
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                                            Event Name <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="name"
                                            placeholder="e.g., Summer Rooftop Party"
                                            value={formData.name}
                                            onChange={handleChange}
                                            className={`w-full px-5 py-3.5 bg-gray-50 dark:bg-gray-900/50 border ${errors.name ? 'border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:ring-indigo-500'} rounded-2xl focus:outline-none focus:ring-2 transition-all dark:text-white`}
                                        />
                                        {errors.name && <p className="mt-2 text-sm text-red-500 font-medium">{errors.name}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                                            Description
                                        </label>
                                        <textarea
                                            name="description"
                                            rows="5"
                                            placeholder="Tell everyone what makes this event special..."
                                            value={formData.description}
                                            onChange={handleChange}
                                            className="w-full px-5 py-3.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 transition-all">
                                <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <MapPinIcon className="h-5 w-5 mr-2 text-indigo-500" />
                                    Location Details
                                </h2>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                                            Venue Name
                                        </label>
                                        <input
                                            type="text"
                                            name="venue"
                                            placeholder="e.g., Central Park Pavilion"
                                            value={formData.location.venue}
                                            onChange={handleLocationChange}
                                            className="w-full px-5 py-3.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                                            City
                                        </label>
                                        <input
                                            type="text"
                                            name="city"
                                            placeholder="e.g., Ljubljana"
                                            value={formData.location.city}
                                            onChange={handleLocationChange}
                                            className="w-full px-5 py-3.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                                            Event Link (URL)
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <LinkIcon className="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input
                                                type="text"
                                                name="url"
                                                placeholder="https://example.com"
                                                value={formData.url}
                                                onChange={handleChange}
                                                className="w-full pl-11 pr-5 py-3.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="space-y-6">
                            <div className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 transition-all">
                                <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <CalendarIcon className="h-5 w-5 mr-2 text-indigo-500" />
                                    Date & Price
                                </h2>

                                <div className="space-y-6">
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                                            Event Date <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            name="holdingDate"
                                            value={formData.holdingDate}
                                            onChange={handleChange}
                                            className={`w-full px-5 py-3.5 bg-gray-50 dark:bg-gray-900/50 border ${errors.holdingDate ? 'border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:ring-indigo-500'} rounded-2xl focus:outline-none focus:ring-2 transition-all dark:text-white`}
                                        />
                                        {errors.holdingDate && <p className="mt-2 text-sm text-red-500 font-medium">{errors.holdingDate}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
                                            Price (€)
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <BanknotesIcon className="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input
                                                type="text"
                                                name="price"
                                                placeholder="Leave empty for Free"
                                                value={formData.price}
                                                onChange={handleChange}
                                                className="w-full pl-11 pr-5 py-3.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 transition-all">
                                <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <PhotoIcon className="h-5 w-5 mr-2 text-indigo-500" />
                                    Event Cover
                                </h2>
                                
                                <div className="space-y-4">
                                    <div 
                                        onClick={() => document.getElementById('file-upload').click()}
                                        className="relative aspect-video rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50 flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 transition-all group overflow-hidden"
                                    >
                                        {previewUrl ? (
                                            <>
                                                <img src={previewUrl} alt="Preview" className="absolute inset-0 w-full h-full object-cover" />
                                                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <PencilSquareIcon className="h-10 w-10 text-white" />
                                                </div>
                                            </>
                                        ) : (
                                            <>
                                                <PhotoIcon className="h-12 w-12 text-gray-400 group-hover:text-indigo-500 transition-colors" />
                                                <p className="mt-2 text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Click to Upload</p>
                                            </>
                                        )}
                                        <input
                                            id="file-upload"
                                            type="file"
                                            name="file"
                                            accept="image/*"
                                            onChange={handleFileChange}
                                            className="hidden"
                                        />
                                    </div>
                                    <p className="text-xs text-gray-400 dark:text-gray-500 font-medium">
                                        Recommended size: 1200 x 675px. Max 5MB.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black py-4 rounded-3xl shadow-xl shadow-indigo-200 dark:shadow-none transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                {loading ? (
                                    <>
                                        <div className="h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                        <span>Creating Event...</span>
                                    </>
                                ) : (
                                    <>
                                        <PlusIcon className="h-5 w-5" />
                                        <span>Create Event</span>
                                    </>
                                )}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}
