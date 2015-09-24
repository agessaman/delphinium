
function drawBoxPlot(min, max, firstQ, median, thirdQ)
{
    analyticsScale = d3.scale.linear()
            .domain([min, max])//maxPoints
            .rangeRound([1, 50])
            .nice(2);
}

function hello(min)
{
//    
//var foo = '{{ min }}';
alert(min);
//    console.log(max);
//    console.log(first);
//    console.log(median);
//    console.log(third);
}