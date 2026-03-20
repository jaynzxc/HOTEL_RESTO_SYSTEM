<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Job Requisition Form</title>
        <!-- Tailwind CSS + Font Awesome -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .transition-all {
                transition: all 0.2s ease;
            }

            .form-card {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .form-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            input:focus,
            select:focus,
            textarea:focus {
                ring: 2px solid #d97706;
                border-color: #d97706;
            }

            .code-block {
                font-family: 'Courier New', monospace;
                background: #1e293b;
                color: #a5f3fc;
            }
        </style>
    </head>

    <body class="bg-slate-50 font-sans antialiased">

        <!-- Simple Header without Sidebar -->
        <div class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-10">
            <div class="max-w-5xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">

                    <!-- LEFT SIDE -->
                    <div class="flex items-center gap-3">

                        <!-- Back Button -->
                        <button onclick="history.back()"
                            class="h-10 w-10 flex items-center justify-center rounded-full border bg-white shadow-sm hover:bg-slate-100 transition">
                            <i class="fas fa-arrow-left text-slate-600"></i>
                        </button>

                        <!-- Logo -->
                        <div
                            class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-md">
                            <i class="fas fa-database text-white text-lg"></i>
                        </div>

                        <!-- Title -->
                        <div>
                            <h1 class="text-xl font-semibold text-slate-800">Core Systems</h1>
                            <p class="text-xs text-slate-500">Job Requisition Management</p>
                        </div>

                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full">
                            <i class="fas fa-key mr-1"></i> Core Access
                        </span>
                        <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full">
                            <i class="fas fa-check-circle mr-1"></i> Post and Get Permissions
                        </span>
                    </div>

                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-6 py-8">
            <!-- Header Section -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                        <i class="fas fa-clipboard-list text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-light text-slate-800">Create New Requisition</h2>
                        <p class="text-sm text-slate-500">Submit a job requisition for approval</p>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden form-card">
                <!-- Form Header -->
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-file-alt text-amber-600"></i>
                        <span class="font-semibold text-slate-700">Requisition Details</span>
                        <span class="text-xs text-slate-400 ml-auto">* Required fields</span>
                    </div>
                </div>

                <!-- Form Body -->
                <form id="requisitionForm" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Job Title -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Job Title <span class="text-amber-600">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="jobTitle" value="Systems Engineer" required
                                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition outline-none"
                                    placeholder="e.g., Network Engineer">
                            </div>
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Department <span class="text-amber-600">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <select id="department" required
                                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition outline-none bg-white appearance-none">
                                    <option value="Hotel" selected>🏨 Hotel</option>
                                    <option value="Restaurant">🍽️ Restaurant</option>
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Requested By -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Requested By <span class="text-amber-600">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="requestedBy" value="Sarah Chen - Core Lead" required
                                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition outline-none">
                            </div>
                        </div>

                        <!-- Number of Positions -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Number of Positions <span class="text-amber-600">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-users absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="number" id="positions" value="2" min="1" required
                                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition outline-none">
                            </div>
                        </div>

                        <!-- Needed By Date -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Needed By <span class="text-amber-600">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="date" id="neededBy" value="2026-12-31" required
                                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition outline-none">
                            </div>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Priority <span class="text-amber-600">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-exclamation-circle absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <select id="priority"
                                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition outline-none bg-white appearance-none">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium" selected>🟡 Medium</option>
                                    <option value="high">🟠 High</option>
                                    <option value="urgent">🔴 Urgent</option>
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Justification -->
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">
                            Justification <span class="text-amber-600">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-align-left absolute left-3 top-3 text-slate-400 text-sm"></i>
                            <textarea id="justification" rows="3" required
                                class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition outline-none resize-none"
                                placeholder="Explain why this position is needed...">Infrastructure upgrade requires additional systems engineer for cloud migration project.</textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-paper-plane"></i>
                        Create Requisition
                    </button>
                </form>
            </div>

            <!-- API Response Section -->
            <div class="mt-6 bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-terminal text-amber-600"></i>
                        <span class="font-semibold text-sm text-slate-700">API Response</span>
                    </div>
                    <button onclick="clearResponse()" class="text-xs text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-trash-alt mr-1"></i> Clear
                    </button>
                </div>
                <pre id="response"
                    class="p-4 text-xs font-mono bg-slate-900 text-green-400 overflow-x-auto max-h-48 overflow-y-auto">// Ready to submit...</pre>
            </div>

            <!-- Footer Note -->
            <div class="mt-6 text-center">
                <a href="#" class="text-xs text-slate-400 hover:text-amber-600 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <script>
            const API_BASE = 'https://humanresource.up.railway.app/api';
            const API_KEY = 'core_system_2026_key_54321';

            // Form submission
            document.getElementById('requisitionForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = {
                    job_title: document.getElementById('jobTitle').value,
                    department: document.getElementById('department').value,
                    requested_by: document.getElementById('requestedBy').value,
                    positions: parseInt(document.getElementById('positions').value),
                    needed_by: document.getElementById('neededBy').value,
                    priority: document.getElementById('priority').value,
                    justification: document.getElementById('justification').value || null
                };

                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';
                submitBtn.disabled = true;

                try {
                    const url = `${API_BASE}/job-requisition.php?api_key=${API_KEY}`;

                    updateResponse('⏳ Submitting requisition to HR system...');

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();
                    updateResponse(JSON.stringify(data, null, 2));

                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            html: `
                            <p>Requisition created successfully!</p>
                            <p class="text-sm mt-2"><strong>Requisition ID:</strong> ${data.data.id}</p>
                            <p class="text-sm"><strong>Job Title:</strong> ${formData.job_title}</p>
                            <p class="text-sm"><strong>Department:</strong> ${formData.department}</p>
                        `,
                            icon: 'success',
                            confirmButtonColor: '#d97706'
                        }).then(() => {
                            // Optionally reset form
                            // document.getElementById('requisitionForm').reset();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.error || 'Failed to create requisition',
                            icon: 'error',
                            confirmButtonColor: '#d97706'
                        });
                    }
                } catch (error) {
                    updateResponse(`Error: ${error.message}`);
                    Swal.fire({
                        title: 'Error',
                        text: error.message,
                        icon: 'error',
                        confirmButtonColor: '#d97706'
                    });
                } finally {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });

            // Update response display
            function updateResponse(content) {
                document.getElementById('response').innerHTML = content;
            }

            // Clear response
            function clearResponse() {
                updateResponse('// Response cleared');
            }

            // Set min date for needed by field
            document.getElementById('neededBy').min = new Date().toISOString().split('T')[0];
        </script>
    </body>

</html>