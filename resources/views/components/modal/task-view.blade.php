<x-modal name="view-task" focusable>
    <div class="p-6 bg-white rounded-lg shadow-md">
        <div class="flex items-start justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800" id="view_title"></h2>
            <span class="px-3 py-1 text-sm rounded-full" id="view_status_badge"></span>
        </div>

        <div class="mb-4">
            <h3 class="mb-1 text-sm font-medium text-gray-700">Description</h3>
            <p class="text-gray-600" id="view_description"></p>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
            <div>
                <h3 class="mb-1 text-sm font-medium text-gray-700">Due Time</h3>
                <p class="text-gray-600" id="view_due_time"></p>
            </div>
            <div>
                <h3 class="mb-1 text-sm font-medium text-gray-700">Created At</h3>
                <p class="text-gray-600" id="view_created_at"></p>
            </div>
        </div>

        <div class="mb-4">
            <h3 class="mb-1 text-sm font-medium text-gray-700">Attachment</h3>
            <div id="view_attachment" class="mt-2"></div>
        </div>

        <div class="flex justify-end mt-6">
            <x-secondary-button @click.prevent="$dispatch('close-modal', 'view-task')">
                Close
            </x-secondary-button>
        </div>
    </div>
</x-modal>
