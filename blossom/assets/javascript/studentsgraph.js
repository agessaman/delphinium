$(document).ready(function(){
  scaleStudentsGraph();
  InitChart();
  InitBox();
});

function scaleStudentsGraph(){

}

function InitChart(){

  var barData = [{
    'x': 50,
    'y': 0
  }, {
    'x': 150,
    'y': 1
  }, {
    'x': 200,
    'y': 1
  }, {
    'x': 250,
    'y': 1
  }, {
    'x': 300,
    'y': 1
  }, {
    'x': 350,
    'y': 2
  }, {
    'x': 400,
    'y': 0
  }, {
    'x': 450,
    'y': 2
  }, {
    'x': 500,
    'y': 0
  }, {
    'x': 550,
    'y': 3
  }, {
    'x': 600,
    'y': 4
  }, {
    'x': 650,
    'y': 3
  }, {
    'x': 700,
    'y': 5
  }, {
    'x': 750,
    'y': 8
  }, {
    'x': 800,
    'y': 8
  }, {
    'x': 850,
    'y': 3
  }, {
    'x': 900,
    'y': 0
  }, {
    'x': 950,
    'y': 0
  }, {
    'x': 1000,
    'y': 0
  }, {
    'x': 1050,
    'y': 0
  }];

  var vis = d3.select('#visualisation'),
    WIDTH = 600,
    HEIGHT = 150,
    MARGINS = {
      top: 20,
      right: 5,
      bottom: 20,
      left: 30
    },
    xRange = d3.scale.ordinal().rangeRoundBands([MARGINS.left, WIDTH - MARGINS.right], 0.1).domain(barData.map(function (d) {
      return d.x;
    })),


    yRange = d3.scale.linear().range([HEIGHT - MARGINS.top, MARGINS.bottom]).domain([0,
      d3.max(barData, function (d) {
        return d.y;
      })
    ]),

    xAxis = d3.svg.axis()
      .scale(xRange)
      .tickSize(5)
      .tickSubdivide(true),

    yAxis = d3.svg.axis()
      .scale(yRange)
      .tickSize(5)
      .orient("left")
      .tickSubdivide(true);


  vis.append('svg:g')
    .attr('class', 'x axis')
    .attr('transform', 'translate(0,' + (HEIGHT - MARGINS.bottom) + ')')
    .call(xAxis);

  vis.append('svg:g')
    .attr('class', 'y axis')
    .attr('transform', 'translate(' + (MARGINS.left) + ',0)')
    .call(yAxis);

  vis.selectAll('rect')
    .data(barData)
    .enter()
    .append('rect')
    .attr('x', function (d) {
      return xRange(d.x);
    })
    .attr('y', function (d) {
      return yRange(d.y);
    })
    .attr('width', xRange.rangeBand())
    .attr('height', function (d) {
      return ((HEIGHT - MARGINS.bottom) - yRange(d.y));
    })
    .attr('fill', 'grey')
    .on('mouseover',function(d){
      d3.select(this)
        .attr('fill','blue');
    })
    .on('mouseout',function(d){
      d3.select(this)
        .attr('fill','grey');
    });

}

d3.box = function() {
    var width = 1,
        height = 1,
        duration = 0,
        domain = null,
        value = Number,
        whiskers = boxWhiskers,
        quartiles = boxQuartiles,
        outlierData = null,
        tickFormat = null;
 
    function box(g) {
        g.each(function(d, i) {
            // sort the data objects by the value function
            d = d.sort(function(a, b) {
                if (value(a) > value(b)) {
                    return 1;
                }
                if (value(a) < value(b)) {
                    return -1;
                }
                if (value(a) === value(b)) {
                    return 0;
                }
            });
 
            var g = d3.select(this).attr('class', 'boxplot'),
                justVals = d.map(value),
                n = d.length,
                min = justVals[0],
                max = justVals[n - 1];
 
            // Compute quartiles. Must return exactly 3 elements.
            var quartileVals = justVals.quartiles = quartiles(justVals);
 
            // Compute whiskers. Must return exactly 2 elements, or null.
            var whiskerIndices = whiskers && whiskers.call(this, justVals, i),
                whiskerData = whiskerIndices && whiskerIndices.map(function(i) {
                    return d[i];
                });
 
            // Compute outliers. If no whiskers are specified, all data are 'outliers'.
            // The outliers are actual data objects, because I'm not concerned with transitions.
            outlierData = whiskerIndices ?
                d.filter(function(d, idx) {
                    return idx < whiskerIndices[0] || idx > whiskerIndices[1];
                }) : d.filter(function() {
                    return true;
                });
 
            // Compute the new x-scale.
            var xScale = d3.scale.linear()
                .domain(domain && domain.call(this, justVals, i) || [min, max])
                .range([0, width]);
 
            // Note: the box, median, and box tick elements are fixed in number,
            // so we only have to handle enter and update. In contrast, the outliers
            // and other elements are variable, so we need to exit them!
            // (Except this is a static chart, so no transitions, so no exiting)
 
            // Update center line: the horizontal line spanning the whiskers.
            var center = g.selectAll('line.center')
                .data(whiskerData ? [whiskerData] : []);
 
            center.enter().insert('line', 'rect')
                .attr('class', 'center-line')
                .attr('x1', function(d) {
                    return xScale(value(d[0]));
                })
                .attr('y1', height / 2)
                .attr('x2', function(d) {
                    return xScale(value(d[1]));
                })
                .attr('y2', height / 2);
 
            // whole innerquartile box. data attached is just quartile values.
            var q1q3Box = g.selectAll('rect.q1q3box')
                .data([quartileVals]);
 
            q1q3Box.enter().append('rect')
                .attr('class', 'box whole-box')
                .attr('y', 0)
                .attr('x', function(d) {
                    return xScale(d[0]);
                })
                .attr('height', height)
                .attr('width', function(d) {
                    return xScale(d[2]) - xScale(d[0]);
                });
 
            // add a median line median line.
            var medianLine = g.selectAll('line.median')
                .data([quartileVals[1]]);
 
            medianLine.enter().append('line')
                .attr('class', 'median')
                .attr('x1', xScale)
                .attr('y1', 0)
                .attr('x2', xScale)
                .attr('y2', height);
 
            // q1-q2 and q2-q3 boxes. attach actual data to these.
            var q1q2Data = d.filter(function(d) {
                return value(d) >= quartileVals[0] && value(d) <= quartileVals[1];
            });
 
            var q1q2Box = g.selectAll('rect.q1q2box')
                .data([q1q2Data]);
 
            q1q2Box.enter().append('rect')
                .attr('class', 'box half-box')
                .attr('y', 0)
                .attr('x', function(d) {
                    return xScale(value(d[0]));
                })
                .attr('width', function(d) {
                    return xScale(value(d[d.length - 1])) - xScale(value(d[0]));
                })
                .attr('height', height);
 
            var q2q3Data = d.filter(function(d) {
                return value(d) > quartileVals[1] && value(d) <= quartileVals[2];
            });
 
            var q2q3Box = g.selectAll('rect.q2q3box')
                .data([q2q3Data]);
 
            q2q3Box.enter().append('rect')
                .attr('class', 'box half-box')
                .attr('y', 0)
                .attr('x', function(d) {
                    return xScale(value(d[0]));
                })
                .attr('width', function(d) {
                    return xScale(value(d[d.length - 1])) - xScale(value(d[0]));
                })
                .attr('height', height);
 
 
            // Whiskers. Attach actual data object
            var whiskerG = g.selectAll('line.whisker')
                .data(whiskerData || [])
                .enter().append('g')
                .attr('class', 'whisker');
 
            whiskerG.append('line')
                .attr('class', 'whisker')
                .attr('x1', function(d) {
                    return xScale(value(d));
                })
                .attr('y1', height / 6)
                .attr('x2', function(d) {
                    return xScale(value(d));
                })
                .attr('y2', height * 5 / 6);
 
            whiskerG.append('text')
                .attr('class', 'label')
                .text(function(d) {
                    return Math.round(value(d));
                })
                .attr('x', function(d) {
                    return xScale(value(d));
                });
 
            whiskerG.append('circle')
                .attr('class', 'whisker')
                .attr('cx', function(d) {
                    return xScale(value(d));
                })
                .attr('cy', height / 2)
                .attr('r', 3);
 
            // Update outliers.
            var outlierG = g.selectAll('g.outlier')
                .data(outlierData)
                .enter().append('g')
                .attr('class', 'outlier');
 
            outlierG.append('circle')
                .attr('class', 'outlier')
                .attr('r', 5)
                .attr('cx', function(d) {
                    return xScale(value(d));
                })
                .attr('cy', height / 2);
 
            outlierG.append('text')
                .attr('class', 'label')
                .text(function(d) {
                    return value(d);
                })
                .attr('x', function(d) {
                    return xScale(value(d));
                });
        });
    }
 
    box.width = function(x) {
        if (!arguments.length) {
            return width;
        }
        width = x;
        return box;
    };
 
    box.height = function(x) {
        if (!arguments.length) {
            return height;
        }
        height = x;
        return box;
    };
 
    box.tickFormat = function(x) {
        if (!arguments.length) {
            return tickFormat;
        }
        tickFormat = x;
        return box;
    };
 
    box.duration = function(x) {
        if (!arguments.length) {
            return duration;
        }
        duration = x;
        return box;
    };
 
    box.domain = function(x) {
        if (!arguments.length) {
            return domain;
        }
        domain = x == null ? x : d3.functor(x);
        return box;
    };
 
    box.value = function(x) {
        if (!arguments.length) {
            return value;
        }
        value = x;
        return box;
    };
 
    box.whiskers = function(x) {
        if (!arguments.length) {
            return whiskers;
        }
        whiskers = x;
        return box;
    };
 
    box.quartiles = function(x) {
        if (!arguments.length) {
            return quartiles;
        }
        quartiles = x;
        return box;
    };
 
    // just a getter. no setting outliers.
    box.outliers = function() {
        return outlierData;
    };
 
    return box;
};
 
function boxWhiskers(d) {
    var q1 = d.quartiles[0],
        q3 = d.quartiles[2],
        iqr = (q3 - q1) * 1.5,
        i = -1,
        j = d.length;
    while (d[++i] < q1 - iqr);
    while (d[--j] > q3 + iqr);
    return [i, j];
}
 
function boxQuartiles(d) {
    return [
        d3.quantile(d, 0.25),
        d3.quantile(d, 0.5),
        d3.quantile(d, 0.75)
    ];
}

function InitBox() {

    var dataArr = constructData();

    var dataField = 'var3';

    var chartRange = cleanUpChartRange(dataArr, dataField);

    var totalWidth = 600,
        totalHeight = 120,
        margin = {
            top: 30,
            right: 5,
            bottom: 50,
            left: 30
        },
        width = totalWidth - margin.left - margin.right,
        height = totalHeight - margin.top - margin.bottom;

    var chart = d3.box()
        .value(function(d) {
            return d[dataField];
        })
        .width(width)
        .height(height)
        .domain([chartRange[0], chartRange[1]]);

    var xScale = d3.scale.linear()
        // this is the data x values
        .domain([chartRange[0], chartRange[1]])
        // this is the svg width
        .range([0, width]);

    var svg = d3.select('.svg-wrapper').selectAll('svg')
        .data([dataArr])
        .enter().append('svg')
            .attr('width', totalWidth)
            .attr('height', totalHeight)
            .append('g')
                .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')')
                .call(chart);

    // axis
    var xAxis = d3.svg.axis()
        .scale(xScale)
        .orient('bottom')
        .ticks(10)
        .tickFormat(tickFormatter);

    // add axis
    svg.append('g')
        .attr('class', 'x axis')
        .attr('transform', 'translate(0,' + (height + 20) + ')')
        .call(xAxis);
}

function constructData() {
    var numPoints = 300;
    var max1 = 1958;
    var max2 = 85731;
    var log3 = 30;

    // make sure the max value is in the data arr.
    // this is just to test what happens on particular max values.
    var arr1 = [max1];
    for (var i = 0; i < numPoints - 1; i++) {
        arr1.push(Math.floor(Math.random() * max1));
    }
    var arr2 = d3.range(numPoints).map(function() {
        return d3.round(d3.random.normal(max2 / 2, max2 / 8)(), 1);
    });
    var arr3 = d3.range(numPoints).map(function() {
        return d3.round(d3.random.logNormal(Math.log(log3), 0.4)(), 1);
    });

    return arr1.map(function(d, i) {
        return {
            myId: i,
            var1: d,
            var2: arr2[i],
            var3: arr3[i]
        };
    });
}

function cleanUpChartRange(arr, field) {

    // calculate the data's min and max so we can use it
    // to make nice bin widths for the histogram
    var xMin = d3.min(arr, function(dataObj) {
        return dataObj[field];
    });
    var xMax = d3.max(arr, function(dataObj) {
        return dataObj[field];
    });

    // construct nice bin widths
    var rounderBin = 20;

    var rounder;
    var tempBinWidth = parseFloat((xMax / rounderBin).toFixed(2));
    var multiplier;
    var places = 2;

    if (tempBinWidth < 1) {
        multiplier = 0.1;
    } else if (tempBinWidth < 2.6) {
        multiplier = 1;
    } else if (tempBinWidth < 10) {
        multiplier = 5;
    } else {
        tempBinWidth = Math.round(tempBinWidth);
        places = tempBinWidth.toString().length - 1;
        multiplier = Math.pow(10, places);
    }

    rounder = Math.round(tempBinWidth / multiplier) * multiplier;

    // clean up rounder so it goes evenly into a power of 10
    if (multiplier > 10) {
        while (Math.pow(10, places + 1) % rounder) {
            rounder += multiplier;
        }
    }
    // round xMax up to the nearest binWidth for the chart max
    return [
        Math.floor(xMin / rounder) * rounder,
        Math.ceil(xMax / rounder) * rounder
    ];
}

function tickFormatter(d) {
    if (d !== (d | 0)) {
        // format non-integers as 1-decimal float
        return d3.format('0.1f')(d);
    } else if (d < 1000) {
        // format just as integers
        return d3.format('d')(d);
    } else if (d < 10000 && (d % 1000 === 0)) {
        // format using SI, to 1 significant digit
        return d3.format('0.1s')(d);
    } else {
        // format using SI, to 2 significant digits
        return d3.format('0.2s')(d);
    }
}