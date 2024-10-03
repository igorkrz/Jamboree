import React, { useState } from "react";
import axios from "axios";

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
    const [file, setFile] = useState(null);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
    };

    const handleLocationChange = (e) => {
        const { name, value } = e.target;
        setFormData({
            ...formData,
            location: { ...formData.location, [name]: value }
        });
    };

    const handleFileChange = (e) => {
        setFormData({ ...formData, file: e.target.files[0] });
        setFile(URL.createObjectURL(e.target.files[0]));
    };

    const validate = () => {
        let tempErrors = {};
        if (!formData.name) tempErrors.name = "Name is required";
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
            await axios.post("/api/custom_events", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });

            alert("Event created successfully!");
            setFormData({
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
        } catch (error) {
            console.error("Error creating event:", error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-2xl mx-auto p-4 bg-white shadow-lg rounded-lg">
            <h2 className="text-2xl font-bold mb-4">Create New Event</h2>
            <form onSubmit={handleSubmit}>
                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold">
                        URL
                    </label>
                    <input
                        type="text"
                        name="url"
                        value={formData.url}
                        onChange={handleChange}
                        className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold">
                        Name
                    </label>
                    <input
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500 ${
                            errors.name ? "border-red-500" : ""
                        }`}
                    />
                    {errors.name && (
                        <p className="text-red-500 text-sm">{errors.name}</p>
                    )}
                </div>

                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold">
                        Description
                    </label>
                    <textarea
                        name="description"
                        value={formData.description}
                        onChange={handleChange}
                        className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    ></textarea>
                </div>

                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold">
                        Price
                    </label>
                    <input
                        type="text"
                        name="price"
                        value={formData.price}
                        onChange={handleChange}
                        className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold">
                        Holding Date
                    </label>
                    <input
                        type="date"
                        name="holdingDate"
                        value={formData.holdingDate}
                        onChange={handleChange}
                        className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500 ${
                            errors.holdingDate ? "border-red-500" : ""
                        }`}
                    />
                    {errors.holdingDate && (
                        <p className="text-red-500 text-sm">{errors.holdingDate}</p>
                    )}
                </div>

                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold">
                        Venue
                    </label>
                    <input
                        type="text"
                        name="venue"
                        value={formData.location.venue}
                        onChange={handleLocationChange}
                        className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold">
                        City
                    </label>
                    <input
                        type="text"
                        name="city"
                        value={formData.location.city}
                        onChange={handleLocationChange}
                        className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold">
                        Upload Image
                    </label>
                    <input
                        type="file"
                        name="file"
                        accept="image/*"
                        onChange={handleFileChange}
                        className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                    <img
                        src={file}
                        width={file ? 250 : 0}
                        height={file ? 250 : 0}
                        alt=""
                    />
                </div>

                <div className="flex justify-end">
                    <button
                        type="submit"
                        disabled={loading}
                        className="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition focus:outline-none disabled:bg-gray-400"
                    >
                        {loading ? "Creating..." : "Create Event"}
                    </button>
                </div>
            </form>
        </div>
    );
}
