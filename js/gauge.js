require(['jquery'], function($) { 

    var gauge = new RadialGauge({
        renderTo: 'gauge',
        width: 250,
        height: 250,
        minValue: 0,
        startAngle: 90,
        ticksAngle: 180,
        valueBox: false,
        maxValue: 10,
        value: $('#gauge').data('perf'),
        majorTicks: [
            "0",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "10"
        ],
        minorTicks: 0.1,
        strokeTicks: true,
        highlights: [
            {
                "from": 0,
                "to": 4,
                "color": "rgba(200, 50, 50, .75)"
            },
            {
                "from": 4,
                "to": 6,
                "color": "rgba(255,255,51)"
            },
            {
                "from": 6,
                "to": 10,
                "color": "rgba(50,205,50)"
            }
        ],
        colorPlate: "#fff",
        borderShadowWidth: 0,
        borders: false,
        needleType: "arrow",
        needleWidth: 2,
        needleCircleSize: 7,
        needleCircleOuter: true,
        needleCircleInner: false,
        animationDuration: 1500,
        animationRule: "linear"
    }).draw();
    
});
