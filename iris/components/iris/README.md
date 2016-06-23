In order to use the Iris component on a page, an LTI configuration component is needed.
Additionally, the size of the iris chart will be the same as the parent element. I.e.,

The code below will create an iris chart that is 500px wide and 400px tall.
```
<div id="iris_chart" style="width:500px; height: 400px;">{% component 'iris' %}</div>
```

If the parent doesn't have a specified height and width, the chart will be 500px X 500px
