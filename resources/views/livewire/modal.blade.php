<div x-show="{{ $showFlag }}"
    class="absolute top-0 left-0 bg-opacity-75 grid items-center justify-center w-screen h-screen bg-gray-200">
    <div
        x-html="{{ $modalContent }}"
        class="rtl w-1/2 h-1/2 m-auto bg-white p-4 border-black rounded overflow-y-auto"
        @click.away="{{ $clickAway }}"
    >
    </div>
</div>
