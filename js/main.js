var sidebar = new ol.control.Sidebar({ element: 'sidebar', position: 'right' });
var jsonFiles, filesLength, fileKey = 0;

var projection = ol.proj.get('EPSG:3857');
var projectionExtent = projection.getExtent();
var size = ol.extent.getWidth(projectionExtent) / 256;
var resolutions = new Array(20);
var matrixIds = new Array(20);
for (var z = 0; z < 20; ++z) {
    // generate resolutions and matrixIds arrays for this WMTS
    resolutions[z] = size / Math.pow(2, z);
    matrixIds[z] = z;
}

function pointStyleFunction(f, r) {
  var p = f.getProperties(), color = '#ff0';
  if(p.mask_adult > 0 && p.mask_child > 0) {
    color = '#0f0';
  } else if(p.mask_adult == 0 && p.mask_child == 0) {
    color = '#f00';
  }
  return new ol.style.Style({
    image: new ol.style.RegularShape({
      radius: 15,
      points: 3,
      fill: new ol.style.Fill({
        color: color
      }),
      stroke: new ol.style.Stroke({
        color: '#fff',
        width: 2
      })
    })
  })
}
var sidebarTitle = document.getElementById('sidebarTitle');
var content = document.getElementById('sidebarContent');

var vectorPoints = new ol.layer.Vector({
  source: new ol.source.Vector({
    url: 'json/points.json',
    format: new ol.format.GeoJSON()
  }),
  style: pointStyleFunction
});
var appView = new ol.View({
  center: ol.proj.fromLonLat([120.221507, 23.000694]),
  zoom: 14
});
var baseLayer = new ol.layer.Tile({
    source: new ol.source.WMTS({
        matrixSet: 'EPSG:3857',
        format: 'image/png',
        url: 'https://wmts.nlsc.gov.tw/wmts',
        layer: 'EMAP',
        tileGrid: new ol.tilegrid.WMTS({
            origin: ol.extent.getTopLeft(projectionExtent),
            resolutions: resolutions,
            matrixIds: matrixIds
        }),
        style: 'default',
        wrapX: true,
        attributions: '<a href="http://maps.nlsc.gov.tw/" target="_blank">國土測繪圖資服務雲</a>'
    }),
    opacity: 0.3
});

var map = new ol.Map({
  layers: [baseLayer, vectorPoints],
  target: 'map',
  view: appView
});
map.addControl(sidebar);
var pointClicked = false;
map.on('singleclick', function(evt) {
  content.innerHTML = '';
  pointClicked = false;
  map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
    if(false === pointClicked) {
      var message = '<table class="table table-dark">';
      message += '<tbody>';
      var p = feature.getProperties();
      message += '<tr><th scope="row" style="width: 100px;">名稱</th><td>' + p.name + '</td></tr>';
      message += '<tr><th scope="row">成人口罩庫存</th><td>' + p.mask_adult + '</td></tr>';
      message += '<tr><th scope="row">兒童口罩庫存</th><td>' + p.mask_child + '</td></tr>';
      message += '<tr><th scope="row">電話</th><td>' + p.phone + '</td></tr>';
      message += '<tr><th scope="row">住址</th><td>' + p.address + '</td></tr>';
      message += '<tr><th scope="row">營業日</th><td>' + p.available + '</td></tr>';
      message += '<tr><th scope="row">備註</th><td>' + p.note + '</td></tr>';
      message += '<tr><th scope="row">更新時間</th><td>' + p.updated + '</td></tr>';
      message += '</tbody></table>';
      sidebarTitle.innerHTML = p.name;
      content.innerHTML = message;
      pointClicked = true;
    }
  });
  sidebar.open('home');
});


var geolocation = new ol.Geolocation({
  projection: appView.getProjection()
});

geolocation.setTracking(true);

geolocation.on('error', function(error) {
  console.log(error.message);
});

var positionFeature = new ol.Feature();

positionFeature.setStyle(new ol.style.Style({
  image: new ol.style.Circle({
    radius: 6,
    fill: new ol.style.Fill({
      color: '#3399CC'
    }),
    stroke: new ol.style.Stroke({
      color: '#fff',
      width: 2
    })
  })
}));

var firstPosDone = false;
geolocation.on('change:position', function() {
  var coordinates = geolocation.getPosition();
  positionFeature.setGeometry(coordinates ? new ol.geom.Point(coordinates) : null);
  if(false === firstPosDone) {
    appView.setCenter(coordinates);
    firstPosDone = true;
  }
});

new ol.layer.Vector({
  map: map,
  source: new ol.source.Vector({
    features: [positionFeature]
  })
});

$('#btn-geolocation').click(function () {
  var coordinates = geolocation.getPosition();
  if(coordinates) {
    appView.setCenter(coordinates);
  }
  return false;
});