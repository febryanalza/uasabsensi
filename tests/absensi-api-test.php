<?php
/**
 * Simple API Test for Absensi Management System
 * Test compatibility dengan shared hosting
 * Jalankan file ini di browser untuk test basic functionality
 */

// Base URL - ganti sesuai domain shared hosting
$base_url = 'http://localhost:8000';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi API Test - Shared Hosting Compatibility</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen py-6">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">🧪 Test API Absensi - Shared Hosting</h1>
            
            <div class="grid md:grid-cols-2 gap-6" x-data="apiTester()">
                <!-- API Statistics Test -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800 mb-3">📊 Test Statistics API</h3>
                    <button @click="testStatistics()" 
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                        Test GET /absensi/api/statistics
                    </button>
                    <div x-show="statisticsResult" x-cloak class="mt-3">
                        <div class="text-xs text-gray-600 bg-gray-100 p-2 rounded overflow-auto max-h-40" x-text="statisticsResult"></div>
                    </div>
                </div>

                <!-- API Company Rules Test -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800 mb-3">🏢 Test Company Rules API</h3>
                    <button @click="testCompanyRules()" 
                            class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
                        Test GET /absensi/api/company-rules
                    </button>
                    <div x-show="companyRulesResult" x-cloak class="mt-3">
                        <div class="text-xs text-gray-600 bg-gray-100 p-2 rounded overflow-auto max-h-40" x-text="companyRulesResult"></div>
                    </div>
                </div>

                <!-- API Karyawan List Test -->
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-purple-800 mb-3">👥 Test Karyawan List API</h3>
                    <button @click="testKaryawanList()" 
                            class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition-colors">
                        Test GET /absensi/api/karyawan-list
                    </button>
                    <div x-show="karyawanListResult" x-cloak class="mt-3">
                        <div class="text-xs text-gray-600 bg-gray-100 p-2 rounded overflow-auto max-h-40" x-text="karyawanListResult"></div>
                    </div>
                </div>

                <!-- API Absensi Data Test -->
                <div class="bg-orange-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-orange-800 mb-3">📋 Test Absensi Data API</h3>
                    <button @click="testAbsensiData()" 
                            class="w-full bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 transition-colors">
                        Test GET /absensi/api/data
                    </button>
                    <div x-show="absensiDataResult" x-cloak class="mt-3">
                        <div class="text-xs text-gray-600 bg-gray-100 p-2 rounded overflow-auto max-h-40" x-text="absensiDataResult"></div>
                    </div>
                </div>

                <!-- Web Route Test -->
                <div class="bg-indigo-50 p-4 rounded-lg md:col-span-2">
                    <h3 class="text-lg font-semibold text-indigo-800 mb-3">🌐 Test Web Routes</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <a href="<?= $base_url ?>/absensi" target="_blank" 
                           class="block bg-indigo-600 text-white text-center px-3 py-2 rounded hover:bg-indigo-700 transition-colors text-sm">
                            Index Absensi
                        </a>
                        <a href="<?= $base_url ?>/absensi/create" target="_blank" 
                           class="block bg-indigo-600 text-white text-center px-3 py-2 rounded hover:bg-indigo-700 transition-colors text-sm">
                            Create Absensi
                        </a>
                        <a href="<?= $base_url ?>/absensi/salary/calculation" target="_blank" 
                           class="block bg-indigo-600 text-white text-center px-3 py-2 rounded hover:bg-indigo-700 transition-colors text-sm">
                            Salary Calculation
                        </a>
                        <a href="<?= $base_url ?>/dashboard" target="_blank" 
                           class="block bg-indigo-600 text-white text-center px-3 py-2 rounded hover:bg-indigo-700 transition-colors text-sm">
                            Dashboard
                        </a>
                    </div>
                </div>

                <!-- Shared Hosting Check -->
                <div class="bg-yellow-50 p-4 rounded-lg md:col-span-2">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-3">🔧 Shared Hosting Compatibility Check</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium text-yellow-700">✅ CDN Resources Used:</h4>
                            <ul class="text-sm text-gray-600 mt-1">
                                <li>• Tailwind CSS (CDN)</li>
                                <li>• Alpine.js (CDN)</li>
                                <li>• Chart.js (CDN)</li>
                                <li>• Lucide Icons (CDN)</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium text-yellow-700">✅ Laravel Features:</h4>
                            <ul class="text-sm text-gray-600 mt-1">
                                <li>• Standard Routing</li>
                                <li>• Blade Templates</li>
                                <li>• HTTP Client for API</li>
                                <li>• Standard Controllers</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-white rounded border-l-4 border-yellow-400">
                        <p class="text-sm text-gray-700">
                            <strong>Note:</strong> Sistem ini dirancang untuk kompatibilitas maximum dengan shared hosting. 
                            Semua dependencies menggunakan CDN dan tidak membutuhkan Node.js atau build process.
                        </p>
                    </div>
                </div>

                <!-- Results Summary -->
                <div class="bg-gray-50 p-4 rounded-lg md:col-span-2" x-show="hasResults">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">📈 Test Results Summary</h3>
                    <div class="space-y-2">
                        <div x-show="statisticsResult" class="text-sm">
                            <span class="font-medium">Statistics API:</span> 
                            <span :class="statisticsResult.includes('error') ? 'text-red-600' : 'text-green-600'">
                                <span x-text="statisticsResult.includes('error') ? 'Failed' : 'Success'"></span>
                            </span>
                        </div>
                        <div x-show="companyRulesResult" class="text-sm">
                            <span class="font-medium">Company Rules API:</span> 
                            <span :class="companyRulesResult.includes('error') ? 'text-red-600' : 'text-green-600'">
                                <span x-text="companyRulesResult.includes('error') ? 'Failed' : 'Success'"></span>
                            </span>
                        </div>
                        <div x-show="karyawanListResult" class="text-sm">
                            <span class="font-medium">Karyawan List API:</span> 
                            <span :class="karyawanListResult.includes('error') ? 'text-red-600' : 'text-green-600'">
                                <span x-text="karyawanListResult.includes('error') ? 'Failed' : 'Success'"></span>
                            </span>
                        </div>
                        <div x-show="absensiDataResult" class="text-sm">
                            <span class="font-medium">Absensi Data API:</span> 
                            <span :class="absensiDataResult.includes('error') ? 'text-red-600' : 'text-green-600'">
                                <span x-text="absensiDataResult.includes('error') ? 'Failed' : 'Success'"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function apiTester() {
            return {
                statisticsResult: '',
                companyRulesResult: '',
                karyawanListResult: '',
                absensiDataResult: '',

                get hasResults() {
                    return this.statisticsResult || this.companyRulesResult || this.karyawanListResult || this.absensiDataResult;
                },

                async testStatistics() {
                    try {
                        const response = await fetch('<?= $base_url ?>/absensi/api/statistics');
                        const data = await response.json();
                        this.statisticsResult = JSON.stringify(data, null, 2);
                    } catch (error) {
                        this.statisticsResult = 'Error: ' + error.message;
                    }
                },

                async testCompanyRules() {
                    try {
                        const response = await fetch('<?= $base_url ?>/absensi/api/company-rules');
                        const data = await response.json();
                        this.companyRulesResult = JSON.stringify(data, null, 2);
                    } catch (error) {
                        this.companyRulesResult = 'Error: ' + error.message;
                    }
                },

                async testKaryawanList() {
                    try {
                        const response = await fetch('<?= $base_url ?>/absensi/api/karyawan-list');
                        const data = await response.json();
                        this.karyawanListResult = JSON.stringify(data, null, 2);
                    } catch (error) {
                        this.karyawanListResult = 'Error: ' + error.message;
                    }
                },

                async testAbsensiData() {
                    try {
                        const response = await fetch('<?= $base_url ?>/absensi/api/data');
                        const data = await response.json();
                        this.absensiDataResult = JSON.stringify(data, null, 2);
                    } catch (error) {
                        this.absensiDataResult = 'Error: ' + error.message;
                    }
                }
            }
        }
    </script>
</body>
</html>