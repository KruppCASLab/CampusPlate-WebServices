function loadChart(name, filter) {
    fetch("reporting.php?action=getPlotData&filter=" + filter).then((response) => {
            return response.json();
        }
    ).then((json) => {
        let data = {
            x: json.x,
            y: json.y,
            type: 'bar',
            marker: {
                color: 'rgb(128,190,57)'
            }
        };
        var layout = {};
        var config = {responsive: true}

        Plotly.newPlot(name, [data], layout, config);
    });
}

loadChart('byDate', 'items');
loadChart('weightByDate', 'weight');