var statsHeight = 240;
var statsWidth = 290;
var trueHeight = 200;
var trueWidth = 250;
var halfWidth = trueWidth / 2;


var positivePace = 20;
var negativePace = 13;
var maxPace = 40;
var minPace = 35;
var positivePaceWidth = positivePace / maxPace * halfWidth;
var negativePaceWidth = negativePace / minPace * halfWidth;
var positivePaceX = halfWidth + (positivePace / 2) + 10;
var negativePaceX = halfWidth - (negativePace / 2) - 10;

var health = 20;
var maxHealth = 50;
var minHealth = 40;
var healthWidth, healthX;
if(health > 0){
  healthWidth = health / maxHealth * halfWidth;
  healthX = halfWidth + (healthWidth / 2) + 10;
}else{
  healthWidth = health / minHealth * halfWidth * -1;
  healthX = halfWidth - (healthWidth / 2) - 10;
}
  

var gap = -75;
var maxGap = 2000;
var minGap = 500;
var gapWidth, gapX;
if(gap > 0){
  gapWidth = gap / maxGap * halfWidth;
  gapX = halfWidth + (gapWidth / 2) + 10;
}else{
  gapWidth = gap / minGap * halfWidth * -1;
  gapX = halfWidth - (gapWidth / 2) - 10;
}
  

var stamina = 80;
var staminaWidth = stamina / 100 * trueWidth;
var staminaX = (staminaWidth / 2) + 10;

scaleStats();
drawStats();

function scaleStats() {
  var statsView = d3.select("#statsView");
  var statsSVG = d3.select("#statsSVG");

  if(statsSize == "small"){
    statsSVG.attr('width', halfWidth + 40)
      .attr('height', trueHeight / 2 + 40);
    statsView.attr('transform', "scale(.5)");
  }else if(statsSize == "medium"){
    statsSVG.attr('width', statsWidth)
      .attr('height', statsHeight);
  }else{
    statsSVG.attr('width', trueWidth * 1.5 + 40)
      .attr('height', trueHeight * 1.5 + 40);
    statsView.attr('transform', "scale(1.5)");
  }
}

function drawStats(){

  var view = d3.select("#statsView");

  drawPace();
  drawHealth();
  drawGap();
  drawStamina();

  function drawPace(){
    drawText(0, "Pace");
    if(positivePace > 0)
      drawPositive(0, positivePaceWidth);
    if(negativePace > 0)
      drawNegative(0, negativePaceWidth);
    drawBox(0);
    drawCenter(0);
    drawNumber(0, positivePaceX, positivePace);
    drawNumber(0, negativePaceX, negativePace);
  }

  function drawHealth(){
    drawText(1, "Health");
    if(health > 0)
      drawPositive(1, healthWidth);
    else
      drawNegative(1, healthWidth);                   
    drawBox(1);
    drawCenter(1);
    drawNumber(1, healthX, health);
  }

  function drawGap(){
    drawText(2, "Gap");
    if(gap > 0)
      drawPositive(2, gapWidth);
    else
      drawNegative(2, gapWidth);                 
    drawBox(2);
    drawCenter(2);
    drawNumber(2, gapX, gap);
  }

  function drawStamina(){
    drawText(3, "Stamina");
    if(statsAnimate){
      view.append('rect')
        .attr('height', 25)
        .attr('width', 0)
        .attr('fill', "white")
        .attr('y', 3 * 50 + 20)
        .transition()
          .delay(4000)
          .duration(1000)
          .attr('width', staminaWidth)
          .attr('fill', "green")
          .ease('bounce');
    }else{
      view.append('rect')
        .attr('height', 25)
        .attr('width', staminaWidth)
        .attr('fill', "green")
        .attr('y', 3 * 50 + 20);
    }
    drawBox(3);
    drawNumber(3, staminaX, stamina);
  }

  function drawText(count, text){
    view.append('text')
      .text(text)
      .attr('y', count * 50 + 10);
  }

  function drawBox(count){
    view.append('rect')
      .attr('height', 25)
      .attr('width', 250)
      .attr('stroke-width', "2")
      .attr('stroke', "black")
      .attr('fill', "none")
      .attr('y', count * 50 + 20);
  }

  function drawCenter(count){
    view.append('rect')
      .attr('height', 25)
      .attr('width', halfWidth)
      .attr('stroke-width', "2")
      .attr('stroke', "black")
      .attr('fill', "none")
      .attr('y', count * 50 + 20);
  }

  function drawPositive(count, value){
    if(statsAnimate){
      view.append('rect')
        .attr('height', 25)
        .attr('width', 0)
        .attr('fill', "white")
        .attr('y', count * 50 + 20)
        .attr('x', trueWidth/2)
        .transition()
          .delay(count * 1000 + 1000)
          .duration(1000)
          .attr('width', value)
          .attr('fill', "green")
          .ease('bounce');
    }else{
      view.append('rect')
        .attr('height', 25)
        .attr('width', value)
        .attr('fill', "green")
        .attr('y', count * 50 + 20)
        .attr('x', trueWidth/2)
    }
  }

  function drawNegative(count, value){
    if(statsAnimate){
      view.append('rect')
        .attr('height', 25)
        .attr('width', 0)
        .attr('fill', "white")
        .attr('y', count * 50 + 20)
        .attr('x', halfWidth)
        .transition()
          .delay(count * 1000 + 1000)
          .duration(1000)
          .attr('x', halfWidth - value)
          .attr('width', value)
          .attr('fill', "red")
          .ease('bounce');
    }else{
      view.append('rect')
        .attr('height', 25)
        .attr('width', value)
        .attr('fill', "red")
        .attr('y', count * 50 + 20)
        .attr('x', halfWidth  - value)
    }
  }

  function drawNumber(count, x, text){
    var text = d3.select('#statsView').append("text")
      .attr("fill", "none")
      .style("text-anchor", "middle")
      .attr('font-size', "20px")
      .attr('x', x)
      .attr('y', count * 50 + 40)
      .text(text);

    text.transition()
      .delay(count * 1000 + 1500)
      .attr("fill", "black");
  }
}