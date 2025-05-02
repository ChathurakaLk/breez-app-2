<x-modal name="create-task" focusable>
    <form id="createTaskForm" enctype="multipart/form-data" class="p-6">
        @csrf
        <h2 class="mb-4 text-lg font-medium text-gray-900">Create Task</h2>

        <!-- Title -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" placeholder="Enter task title"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            <span class="text-sm text-red-500 error-title"></span>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="3" placeholder="Enter task description"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"></textarea>
            <span class="text-sm text-red-500 error-description"></span>
        </div>

        <!-- Due Time -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Due Date & Time</label>
            <input type="datetime-local" name="time" placeholder="Select due date and time"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            <span class="text-sm text-red-500 error-due_time"></span>
        </div>

        <!-- Attachment -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Attachment</label>
            <input type="file" name="attachment" accept="image/*"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            <span class="text-sm text-red-500 error-attachment"></span>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end mt-6">
            <x-secondary-button @click.prevent="$dispatch('close-modal', 'create-task')">Cancel</x-secondary-button>
            <x-primary-button class="ml-3" type="submit">Create</x-primary-button>
        </div>
    </form>
</x-modal>

@push('scripts')
    <script>
        window.addEventListener('close-modal', function() {
            $('#createTaskForm')[0].reset();
            $('.error-title, .error-description, .error-due_time, .error-attachment').text('');
        });

        $('#createTaskForm').submit(function(e) {
            e.preventDefault();

            // Clear previous error messages
            $('.error-title, .error-description, .error-due_time, .error-attachment').text('');

            // Frontend validation
            let title = $('[name="title"]').val().trim();
            let description = $('[name="description"]').val().trim();
            let dueTime = $('[name="time"]').val().trim();
            let isValid = true;

            if (title === '') {
                $('.error-title').text('Title is required.');
                isValid = false;
            }

            if (dueTime === '') {
                $('.error-due_time').text('Due time is required.');
                isValid = false;
            }

            if (!isValid) return;

            // Submit AJAX if frontend validation passed
            let formData = new FormData(this);


            $.ajax({
                url: '/api/tasks',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    Swal.fire('Success', 'Task created successfully!', 'success');
                    $('#createTaskForm')[0].reset();
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'create-task'
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
