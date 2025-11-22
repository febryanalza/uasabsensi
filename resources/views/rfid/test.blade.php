<!DOCTYPE html>
<html>
<head>
    <title>RFID API Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">RFID API Debug Test</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <button id="testCount" class="bg-blue-500 text-white p-3 rounded hover:bg-blue-600">
                Test Count
            </button>
            <button id="testStats" class="bg-green-500 text-white p-3 rounded hover:bg-green-600">
                Test Statistics
            </button>
            <button id="testData" class="bg-purple-500 text-white p-3 rounded hover:bg-purple-600">
                Test Data
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <h3 class="font-semibold mb-2">Count Test Result:</h3>
                <pre id="countResult" class="bg-gray-100 p-3 rounded text-sm overflow-auto"></pre>
            </div>
            
            <div>
                <h3 class="font-semibold mb-2">Statistics Test Result:</h3>
                <pre id="statsResult" class="bg-gray-100 p-3 rounded text-sm overflow-auto"></pre>
            </div>
            
            <div>
                <h3 class="font-semibold mb-2">Data Test Result:</h3>
                <pre id="dataResult" class="bg-gray-100 p-3 rounded text-sm overflow-auto"></pre>
            </div>
        </div>
    </div>

    <script>
        function updateResult(id, result) {
            document.getElementById(id).textContent = typeof result === 'string' ? result : JSON.stringify(result, null, 2);
        }

        document.getElementById('testCount').addEventListener('click', async () => {
            try {
                const response = await fetch('/rfid-test/count', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                updateResult('countResult', data);
            } catch (error) {
                updateResult('countResult', 'Error: ' + error.message);
            }
        });

        document.getElementById('testStats').addEventListener('click', async () => {
            try {
                const response = await fetch('/rfid-test/statistics', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                updateResult('statsResult', data);
            } catch (error) {
                updateResult('statsResult', 'Error: ' + error.message);
            }
        });

        document.getElementById('testData').addEventListener('click', async () => {
            try {
                const response = await fetch('/rfid-test/data', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                updateResult('dataResult', data);
            } catch (error) {
                updateResult('dataResult', 'Error: ' + error.message);
            }
        });
    </script>
</body>
</html>