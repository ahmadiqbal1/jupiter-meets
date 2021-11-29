let donutChartCanvas = $("#usersChart").get(0).getContext("2d");
let donutData = {
    labels: ["Free", "Paid"],
    datasets: [
        {
            data: [freeUsers, paidUsers],
            backgroundColor: ["#f56954", "#f39c12"],
        },
    ],
};

let donutOptions = {
    maintainAspectRatio: false,
    responsive: true,
    plugins: {
      labels: {
        fontColor: '#fff'
      }
    }
};

new Chart(donutChartCanvas, {
    type: "doughnut",
    data: donutData,
    options: donutOptions,
});

let incomeChartData = {
      labels  : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label               : 'Income' + currentYear,
          backgroundColor     : '#f39c12',
          borderColor         : '#f39c12',
          data                : []
        },
      ]
    };

for (let i = 1; i <= 12; i++) {
  incomeChartData.datasets[0].data[i-1] = montlyIncome[i] || 0;
}

let incomeChartCanvas = $('#incomeChart').get(0).getContext('2d');
let incomeChart_ = $.extend(true, {}, incomeChartData);
incomeChart_.datasets[0] = incomeChartData.datasets[0];

let chartOptions = {
  responsive              : true,
  maintainAspectRatio     : false,
  datasetFill             : false,
  plugins: {
      labels: {
        render: function () {
          return ''
        },
        fontColor: '#000'
      }
    }
};

new Chart(incomeChartCanvas, {
  type: 'bar',
  data: incomeChart_,
  options: chartOptions
});

let userGraphData = {
      labels  : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label               : 'User Registration' + currentYear,
          backgroundColor     : '#f39c12',
          borderColor         : '#f39c12',
          data                : []
        },
      ]
    };

for (let i = 1; i <= 12; i++) {
  userGraphData.datasets[0].data[i-1] = userGraph[i] || 0;
}

let userGraphCanvas = $('#userGraph').get(0).getContext('2d');
let userGraph_ = $.extend(true, {}, userGraphData);
userGraph_.datasets[0] = userGraphData.datasets[0];

new Chart(userGraphCanvas, {
  type: 'bar',
  data: userGraph_,
  options: chartOptions
});

let meetingGraphData = {
      labels  : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label               : 'Meetings' + currentYear,
          backgroundColor     : '#f39c12',
          borderColor         : '#f39c12',
          data                : []
        },
      ]
    };

for (let i = 1; i <= 12; i++) {
  meetingGraphData.datasets[0].data[i-1] = meetingGraph[i] || 0;
}

let meetingGraphCanvas = $('#meetingGraph').get(0).getContext('2d');
let meetingGraph_ = $.extend(true, {}, meetingGraphData);
meetingGraph_.datasets[0] = meetingGraphData.datasets[0];

new Chart(meetingGraphCanvas, {
  type: 'bar',
  data: meetingGraph_,
  options: chartOptions
});