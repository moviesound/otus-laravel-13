<table>

    <thead>
    <tr>

        <th>Date</th>

        <th>User ID</th>

        <th>Action</th>

        <th>IP</th>

        <th>User Agent</th>

    </tr>
    </thead>

    <tbody>

    @forelse ($logs as $log)

        <tr>

            <td>
                {{ $log->created_at }}
            </td>

            <td>
                {{ $log->user_id }}
            </td>

            <td class="mono">
                {{ $log->action }}
            </td>

            <td>
                {{ $log->ip }}
            </td>

            <td>
                {{ \Illuminate\Support\Str::limit($log->user_agent, 80) }}
            </td>

        </tr>

    @empty

        <tr>

            <td colspan="5">
                No logs
            </td>

        </tr>

    @endforelse

    </tbody>

</table>

<div style="margin-top: 15px;">

    {{ $logs->links() }}

</div>