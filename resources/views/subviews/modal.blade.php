<div x-show="{{ $showFlag }}"
    class="fixed top-0 left-0 bg-opacity-75 flex items-center justify-center w-screen h-screen bg-gray-200 z-50">
    <div
        x-html="{{ $modalContent }}"
        class="{{ $classes }} w-1/2 h-1/2 m-auto bg-white p-4 border-black rounded overflow-y-auto"
        @click.outside="{{ $clickAway }}"
    >
    </div>
</div>
