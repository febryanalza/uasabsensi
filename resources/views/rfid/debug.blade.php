@extends('layouts.dashboard')

@section('title', 'RFID Debug Test')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">RFID Management Debug Test</h2>
        
        <div class="space-y-4">
            <div>
                <button id="testStats" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Test Statistics API
                </button>
                <pre id="statsResult" class="mt-2 p-3 bg-gray-100 rounded text-sm"></pre>
            </div>
            
            <div>
                <button id="testData" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Test Data API
                </button>
                <pre id="dataResult" class="mt-2 p-3 bg-gray-100 rounded text-sm"></pre>
            </div>

            <div>
                <button id="testCount" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Test Direct Count
                </button>
                <pre id="countResult" class="mt-2 p-3 bg-gray-100 rounded text-sm"></pre>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('testStats').addEventListener('click', async () => {
    try {
        const response = await fetch('{{ route("rfid.statistics") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        document.getElementById('statsResult').textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        document.getElementById('statsResult').textContent = 'Error: ' + error.message;
    }
});

document.getElementById('testData').addEventListener('click', async () => {
    try {
        const response = await fetch('{{ route("rfid.data") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        document.getElementById('dataResult').textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        document.getElementById('dataResult').textContent = 'Error: ' + error.message;
    }
});

document.getElementById('testCount').addEventListener('click', async () => {
    try {
        const response = await fetch('{{ url("/rfid/test-count") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        document.getElementById('countResult').textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        document.getElementById('countResult').textContent = 'Error: ' + error.message;
    }
});
</script>
@endsection