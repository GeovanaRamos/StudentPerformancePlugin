var config = {
  type: 'gauge',
  data: {
    labels: ['Ruim', 'Regular', 'Ótimo'],
    datasets: [{
      data: [40,50,100],
      value: document.getElementById('chart').getAttribute('data-perf'),
      backgroundColor: ['red', 'yellow', 'green'],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    title: {
      display: false,
      text: 'Ritmo do Aluno no Curso'
    },
    layout: {
      padding: {
        bottom: 30
      }
    },
    needle: {
      // Needle circle radius as the percentage of the chart area width
      radiusPercentage: 2,
      // Needle width as the percentage of the chart area width
      widthPercentage: 3.2,
      // Needle length as the percentage of the interval between inner radius (0%) and outer radius (100%) of the arc
      lengthPercentage: 80,
      // The color of the needle
      color: 'rgba(0, 0, 0, 1)'
    },
    valueLabel: {
      display: false
    },
    plugins: {
      datalabels: {
        display: true,
        formatter:  function (value, context) {
          return context.chart.data.labels[context.dataIndex];
        },
        //color: function (context) {
        //  return context.dataset.backgroundColor;
        //},
        color: 'rgba(0, 0, 0, 1.0)',
        //color: 'rgba(255, 255, 255, 1.0)',
        backgroundColor: null,
        font: {
          size: 12,
          weight: 'bold'
        }
      }
    }
  }
};

window.onload = function() {
  var ctx = document.getElementById('chart').getContext('2d');
  window.myGauge = new Chart(ctx, config);
};


