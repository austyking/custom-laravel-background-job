<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="flex flex-col">
                        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="overflow-hidden">
                                    <table class="min-w-full">
                                        <thead class="bg-white border-b">
                                        <tr>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">#ID</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">Class</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">Method</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">Parameters</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">Status</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">PID</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">Retry Count</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">Error Message</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">Actions</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($jobs as $job)
                                            <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">{{ $job->id }}</td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">{{ $job->class }}</td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">{{ $job->method }}</td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">{{ json_encode($job->parameters) }}</td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                                    <span class="@if($job->status === 'running') text-green-500 @elseif($job->status === 'cancelled') text-red-500 @else text-gray-500 @endif">
                                                        {{ \Illuminate\Support\Str::ucfirst($job->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">{{ $job->pid }}</td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">{{ $job->retry_count }}</td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">{{ $job->error_message }}</td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                                    @if ($job->status === 'running')
                                                        <form action="{{ route('runner.jobs.cancel', $job->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                                        </form>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
