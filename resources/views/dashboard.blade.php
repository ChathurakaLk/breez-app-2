<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                {{ __('My Tasks') }}
            </h2>
            <button x-data="" @click.prevent="$dispatch('open-modal', 'create-task')"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                + Create Task
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="taskTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Title
                                    </th>
                                    <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Due Time
                                    </th>
                                    <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="taskList" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                    <div id="noTasks" class="hidden mt-4 text-center text-gray-500">No tasks found.</div>
                </div>
            </div>
        </div>
    </div>

    @component('components.modal.task-create')
    @endcomponent

    @component('components.modal.task-edit')
    @endcomponent

    @component('components.modal.task-view')
    @endcomponent

    @push('scripts')
        <script>
            const FILESYSTEM_URL = "{{ env('FILESYSTEM_URL') }}";

            $(document).ready(function() {
                fetchTasks();
            });

            function fetchTasks() {
                $.ajax({
                    url: '/api/tasks',
                    method: 'GET',
                    success: function(tasks) {
                        if (tasks.length === 0) {
                            $('#taskList').empty();
                            $('#noTasks').removeClass('hidden');
                            return;
                        }

                        $('#noTasks').addClass('hidden');
                        let html = '';
                        tasks.forEach(task => {
                            const status = task.status ?? 'Pending';
                            const statusClass = getStatusColorClass(status);

                            html += `
                                    <tr>
                                        <td class="px-4 py-2">${task.title}</td>
                                        <td class="px-4 py-2">${task.time ?? 'N/A'}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-3 py-1 text-sm rounded-full ${statusClass}">
                                            ${status}
                                        </span>
                                            </td>
                                        <td class="px-4 py-2 space-x-2">
                                              <button onclick="openViewModal(${task.id})" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="openEditModal(${task.id})" class="text-yellow-500 hover:text-yellow-700">
                                                    <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteTask(${task.id})" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>                                    </tr>
                                `;
                        });
                        $('#taskList').html(html);
                    },
                    error: function() {
                        Swal.fire('Error', 'Unable to fetch tasks. Please try again later.', 'error');
                    }
                });
            }

            function openEditModal(taskId) {
                $.ajax({
                    url: `/api/tasks/${taskId}`,
                    method: 'GET',
                    success: function(task) {
                        console.log(task);

                        $('#edit_task_id').val(task.id);
                        $('#edit_title').val(task.title);
                        $('#edit_description').val(task.description);
                        $('#edit_status').val(task.status);
                        $('#edit_due_time').val(task.time);

                        // Show current attachment if exists
                        if (task.attachment) {
                            $('#current_attachment').html(`
                            <p class="text-sm text-gray-600">Current file:
                                <a href="${FILESYSTEM_URL}/storage/${task.attachment}" target="_blank" class="text-blue-500 underline">View</a>
                            </p>
                        `);
                        } else {
                            $('#current_attachment').html(
                                '<p class="text-sm text-gray-600">No file attached</p>');
                        }

                        // Open modal
                        window.dispatchEvent(new CustomEvent('open-modal', {
                            detail: 'edit-task'
                        }));
                    },
                    error: function() {
                        Swal.fire('Error', 'Unable to fetch task details', 'error');
                    }
                });
            }

            function deleteTask(taskId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/tasks/${taskId}`,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {
                                Swal.fire('Deleted!', 'Your task has been deleted.', 'success');
                                fetchTasks();
                            },
                            error: function() {
                                Swal.fire('Error', 'Unable to delete task', 'error');
                            }
                        });
                    }
                });
            }

            function openViewModal(taskId) {
                $.ajax({
                    url: `/api/tasks/${taskId}`,
                    method: 'GET',
                    success: function(task) {
                        // Populate modal fields
                        $('#view_title').text(task.title || 'No title');
                        $('#view_description').text(task.description || 'No description');
                        $('#view_due_time').text(task.time || 'Not set');
                        $('#view_created_at').text(new Date(task.created_at).toLocaleString());

                        // Set status badge with appropriate color
                        const status = task.status || 'Pending';
                        $('#view_status_badge')
                            .text(status)
                            .removeClass()
                            .addClass(`px-3 py-1 text-sm rounded-full ${getStatusColorClass(status)}`);

                        // Show attachment if exists
                        if (task.attachment) {
                            $('#view_attachment').html(`
                                <div class="mt-4 space-y-2">
                                    <img src="${FILESYSTEM_URL}/storage/${task.attachment}"
                                        alt="Attachment"
                                        class="object-cover w-32 h-32 border rounded shadow" />

                                    <a href="${FILESYSTEM_URL}/storage/${task.attachment}"
                                    download
                                    class="inline-block px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
                                         <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            `);
                        } else {
                            $('#view_attachment').html('<p class="mt-4 text-gray-500">No attachment</p>');
                        }




                        // Open modal
                        window.dispatchEvent(new CustomEvent('open-modal', {
                            detail: 'view-task'
                        }));
                    },
                    error: function() {
                        Swal.fire('Error', 'Unable to fetch task details', 'error');
                    }
                });
            }

            function getStatusColorClass(status) {
                switch (status.toLowerCase()) {
                    case 'completed':
                        return 'bg-green-100 text-green-800';
                    case 'in progress':
                        return 'bg-blue-100 text-blue-800';
                    case 'pending':
                        return 'bg-yellow-100 text-yellow-800';
                    default:
                        return 'bg-gray-100 text-gray-800';
                }
            }
        </script>
    @endpush
</x-app-layout>
