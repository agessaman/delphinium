alert("here");
$(document).ready(function() {
   /* 
    var tree = d3.layout.tree()
    .sort(null)
    //.size([size.height, size.width - maxLabelLength*options.fontSize])
    .children(function(d)
    {
        return (!d.children || d.children.length === 0) ? null : d.children;
    });

var nodes = tree.nodes(moduleData);
var links = tree.links(nodes);
  
    */
   
   var data = [
  {name: "foo", links: ["a", "b", "c"]},
  {name: "bar", links: ["d", "e", "f"]}
];

var toplist = d3.select("ul");

toplist.selectAll("li")
    .data(data)
  .enter().append("li")
    .text(function(d) { return d.name; })
    .on("click", expand);

    function expand(d) {
      d3.select(this)
          .on("click", null)
        .append("ul")
        .selectAll("li")
          .data(d.links)
        .enter().append("li")
          .text(function(d) { return d; });
    }

    
});

