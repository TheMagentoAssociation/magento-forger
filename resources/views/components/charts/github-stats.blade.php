<script>
    const labels = {!! json_encode(array_keys($monthlyStats), JSON_THROW_ON_ERROR) !!};

    const prOpenedData = {!! json_encode(array_column($monthlyStats, 'pr_opened'), JSON_THROW_ON_ERROR) !!};
    const prClosedData = {!! json_encode(array_column($monthlyStats, 'pr_closed'), JSON_THROW_ON_ERROR) !!};

    const issueOpenedData = {!! json_encode(array_column($monthlyStats, 'issue_opened'), JSON_THROW_ON_ERROR) !!};
    const issueClosedData = {!! json_encode(array_column($monthlyStats, 'issue_closed'), JSON_THROW_ON_ERROR) !!};

    const createBarChart = (ctx, label1, data1, color1, label2, data2, color2) => {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: label1,
                        data: data1,
                        backgroundColor: color1,
                    },
                    {
                        label: label2,
                        data: data2,
                        backgroundColor: color2,
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                        }
                    }
                }
            }
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        createBarChart(
            document.getElementById('prChart').getContext('2d'),
            'PRs Opened', prOpenedData, 'rgba(54, 162, 235, 0.6)',
            'PRs Closed', prClosedData, 'rgba(255, 99, 132, 0.6)'
        );

        createBarChart(
            document.getElementById('issueChart').getContext('2d'),
            'Issues Opened', issueOpenedData, 'rgba(153, 102, 255, 0.6)',
            'Issues Closed', issueClosedData, 'rgba(255, 159, 64, 0.6)'
        );
        fetch('/api/charts/prAgeOverTime')
            .then(res => res.json())
            .then(config => {
                const ctx = document.getElementById('prAgeOverTime').getContext('2d');
                new Chart(ctx, config);
            });
        fetch('/api/charts/issueAgeOverTime')
            .then(res => res.json())
            .then(config => {
                const ctx = document.getElementById('issueAgeOverTime').getContext('2d');
                new Chart(ctx, config);
            });
    });
</script>
