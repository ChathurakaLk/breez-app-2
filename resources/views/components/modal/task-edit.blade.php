<x-modal name="edit-task" focusable x-on:close="$dispatch('close-modal', 'edit-task')">
    <form id="editTaskForm" enctype="multipart/form-data" class="p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="task_id" id="edit_task_id">

        <h2 class="mb-4 text-lg font-medium text-gray-900">Edit Task</h2>

        <!-- Title -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" id="edit_title"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            <span class="text-sm text-red-500 error-title"></span>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="edit_description" rows="3"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"></textarea>
            <span class="text-sm text-red-500 error-description"></span>
        </div>

        <!-- Due Time -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Due Time</label>
            <input type="datetime-local" name="time" id="edit_due_time"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            <span class="text-sm text-red-500 error-due_time"></span>
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="edit_status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
            <span class="text-sm text-red-500 error-status"></span>
        </div>


        <!-- Attachment -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Attachment</label>
            <input type="file" name="attachment" accept="image/*"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            <span class="text-sm text-red-500 error-attachment"></span>
            <div id="current_attachment" class="mt-2"></div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end mt-6">
            <x-secondary-button @click.prevent="$dispatch('close-modal', 'edit-task')">Cancel</x-secondary-button>
            <x-primary-button class="ml-3" type="submit">Update</x-primary-button>
        </div>
    </form>
</x-modal>

@push('scripts')
    <script>
        $('#editTaskForm').submit(function(e) {
            e.preventDefault();

            // Clear previous error messages
            $('.error-title, .error-description, .error-due_time, .error-attachment').text('');

            let formData = new FormData(this);
            let taskId = $('#edit_task_id').val();

            $.ajax({
                url: `/api/tasks/${taskId}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    Swal.fire('Success', 'Task updated successfully!', 'success');
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'edit-task'
                    }));
                    fetchTasks();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        if (errors.title) {
                            $('.error-title').text(errors.title[0]);
                        }
                        if (errors.description) {
                            $('.error-description').text(errors.description[0]);
                        }
                        if (errors.due_time) {
                            $('.error-due_time').text(errors.due_time[0]);
                        }
                        if (errors.attachment) {
                            $('.error-attachment').text(errors.attachment[0]);
                        }
                    } else {
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    }
                }
            });
        });
    </script>
@endpush
