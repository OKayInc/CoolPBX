document.addEventListener("DOMContentLoaded", function()
{
    const canvases = document.querySelectorAll("canvas.dashboard");

    canvases.forEach((canvas) =>
    {
        const ctx = canvas.getContext("2d");

        const type = canvas.dataset.type;
        const labels = JSON.parse(canvas.dataset.labels || "[]");
        const values = JSON.parse(canvas.dataset.values || "[]");
        const colors = JSON.parse(canvas.dataset.colors || "[]");
        const count = canvas.dataset.count || "";

        const config = {
            type: type,
            data: {
                labels: labels,
                datasets: [
                    {
                        data: values,
                        backgroundColor: colors,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: type !== "doughnut" },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(ctx) {
                                return `Total: ${ctx.formattedValue}`;
                            },
                            afterLabel: function(ctx) {
                                const extras = JSON.parse(ctx.chart.canvas.dataset.extra || "[]");
                                const extra = extras[ctx.dataIndex] ?? [];

                                if (!extra.length) return "";

                                return [
                                    "",
                                    "Agents:",
                                    ...extra
                                ];
                            }
                        }
                    }
                },
            },
            plugins: [],
        };

        switch(type)
        {
            case "doughnut":
                config.options.cutout = "75%";
                config.plugins.push({
                    beforeDraw: (chart) =>
                    {
                        const { width, height } = chart;
                        const context = chart.ctx;
                        context.restore();

                        if(count)
                        {
                            context.font = "bold 28px sans-serif";
                            context.textBaseline = "middle";
                            context.fillStyle = "#000";
                            const textX = Math.round(
                                (width - context.measureText(count).width) / 2
                            );
                            const textY = height / 2 - 10;
                            context.fillText(count, textX, textY);
                        }

                        context.save();
                    },
                });

                break;
        }

        const chart = new Chart(ctx, config);

        canvas.addEventListener("click", function(event)
        {
            const points = chart.getElementsAtEventForMode(event, "nearest", { intersect: true }, false);

            if(points.length)
            {
                const index = points[0].index;

                const links = JSON.parse(canvas.dataset.links || "[]");
                const url = links[index] ?? null;

                if(url)
                {
                    window.location.href = url;
                }
            }
        });

    });
});
