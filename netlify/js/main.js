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

function pointStyleFunction(f) {
  var p = f.getProperties(), color, stroke, radius;
  if(f === currentFeature) {
    stroke = new ol.style.Stroke({
      color: '#000',
      width: 5
    });
    radius = 25;
  } else {
    stroke = new ol.style.Stroke({
      color: '#fff',
      width: 2
    });
    radius = 15;
  }
  if(p.updated === '') {
    color = '#ccc';
  } else if(p.mask_adult > 100 && p.mask_child > 25) {
    color = '#48c774'; // > 50% stock
  } else if(p.mask_adult > 40 && p.mask_child > 10) {
    color = '#ffdd57'; // > 20% stock
  } else if(p.mask_adult > 20 && p.mask_child > 5) {
    color = '#fc82b1'; // > 10% stock
  } else {
    color = '#f00'; // < 10% stock, treat as 0
  }
  return new ol.style.Style({
    image: new ol.style.RegularShape({
      radius: radius,
      points: 3,
      fill: new ol.style.Fill({
        color: color
      }),
      stroke: stroke
    })
  })
}
var sidebarTitle = document.getElementById('sidebarTitle');
var content = document.getElementById('sidebarContent');

var appView = new ol.View({
  center: ol.proj.fromLonLat([120.221507, 23.000694]),
  zoom: 14
});

var vectorPoints = new ol.layer.Vector({
  source: new ol.source.Vector({
    format: new ol.format.GeoJSON({
      featureProjection: appView.getProjection()
    })
  }),
  style: pointStyleFunction
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
    opacity: 0.8
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
      var p = feature.getProperties();
      var targetHash = '#' + p.id;
      if (window.location.hash !== targetHash) {
        window.location.hash = targetHash;
      }
      pointClicked = true;
    }
  });
});

var previousFeature = false;
var currentFeature = false;
function showPoint(pointId) {
  $('#findPoint').val(pointId);
  selectedCounty = '';
  adminTreeChange();
  
  var features = vectorPoints.getSource().getFeatures();
  var pointFound = false;
  for(k in features) {
    var p = features[k].getProperties();
    if(p.id === pointId) {
      currentFeature = features[k];
      features[k].setStyle(pointStyleFunction(features[k]));
      if(false !== previousFeature) {
        previousFeature.setStyle(pointStyleFunction(previousFeature));
      }
      previousFeature = currentFeature;
      appView.setCenter(features[k].getGeometry().getCoordinates());
      appView.setZoom(15);
      var lonLat = ol.proj.toLonLat(p.geometry.getCoordinates());
      var message = '<table class="table table-dark">';
      message += '<tbody>';
      message += '<tr><th scope="row" style="width: 100px;">名稱</th><td>';
      message += '<a href="http://www.nhi.gov.tw/QueryN/Query3_Detail.aspx?HospID=' + p.id + '" target="_blank">' + p.name + '</a>';
      if(p.website != '') {
        message += ' &nbsp; <a href="' + p.website + '" target="_blank" class="pull-right">(網站)</a>';
      }
      message += '</td></tr>';
      if(p.updated === '') {
        message += '<tr><th scope="row">成人口罩庫存</th><td>無資料</td></tr>';
        message += '<tr><th scope="row">兒童口罩庫存</th><td>無資料</td></tr>';
      } else {
        message += '<tr><th scope="row">成人口罩庫存</th><td>' + p.mask_adult + '</td></tr>';
        message += '<tr><th scope="row">兒童口罩庫存</th><td>' + p.mask_child + '</td></tr>';
      }
      if(p.custom_note != '') {
        message += '<tr><th scope="row">口罩銷售提醒</th><td>' + p.custom_note + '</td></tr>';
      }
      message += '<tr><th scope="row">備註</th><td>' + p.note.replace(/\\n/g, '<br />') + '</td></tr>';
      message += '<tr><th scope="row">電話</th><td>' + p.phone + '</td></tr>';
      message += '<tr><th scope="row">住址</th><td>' + p.address + '</td></tr>';
      message += '<tr><th scope="row">更新時間</th><td>' + p.updated + '</td></tr>';
      message += '<tr><td colspan="2">';
      if(p.service_periods != '') {
        var sParts = p.service_periods.split('');
        message += '<table class="table table-bordered text-center" style="color: black;">';
        message += '<thead class="table-dark"><tr><th></th><th>一</th><th>二</th><th>三</th><th>四</th><th>五</th><th>六</th><th>日</th></tr></thead><tbody>';
        message += '<tr><td class="table-dark">上</td>';
        for(i = 0; i < 7; i++) {
          if(sParts[i] == 'N') {
            message += '<td class="table-success"><i class="fa fa-check-circle"></i></td>';
          } else {
            message += '<td class="table-danger"><i class="fa fa-times-circle"></i></td>';
          }
        }
        message += '</tr>';
        message += '<tr><td class="table-dark">下</td>';
        for(i = 7; i < 14; i++) {
          if(sParts[i] == 'N') {
            message += '<td class="table-success"><i class="fa fa-check-circle"></i></td>';
          } else {
            message += '<td class="table-danger"><i class="fa fa-times-circle"></i></td>';
          }
        }
        message += '</tr>';
        message += '<tr><td class="table-dark">晚</td>';
        for(i = 14; i < 21; i++) {
          if(sParts[i] == 'N') {
            message += '<td class="table-success"><i class="fa fa-check-circle"></i></td>';
          } else {
            message += '<td class="table-danger"><i class="fa fa-times-circle"></i></td>';
          }
        }
        message += '</tr>';
        message += '</tbody></table>';
      }
      message += '<hr /><div class="btn-group-vertical" role="group" style="width: 100%;">';
      message += '<a href="https://www.google.com/maps/dir/?api=1&destination=' + lonLat[1] + ',' + lonLat[0] + '&travelmode=driving" target="_blank" class="btn btn-info btn-lg btn-block">Google 導航</a>';
      message += '<a href="https://wego.here.com/directions/drive/mylocation/' + lonLat[1] + ',' + lonLat[0] + '" target="_blank" class="btn btn-info btn-lg btn-block">Here WeGo 導航</a>';
      message += '<a href="https://bing.com/maps/default.aspx?rtp=~pos.' + lonLat[1] + '_' + lonLat[0] + '" target="_blank" class="btn btn-info btn-lg btn-block">Bing 導航</a>';
      message += '</div></td></tr>';
      message += '</tbody></table>';
      sidebarTitle.innerHTML = p.name;
      content.innerHTML = message;
    }
  }
  sidebar.open('home');
}

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
  } else {
    alert('目前使用的設備無法提供地理資訊');
  }
  return false;
});

var pointsFc;
var adminTree = {};
var findTerms = [];
$.getJSON('json/points.json', {}, function(c) {
  pointsFc = c;
  var vSource = vectorPoints.getSource();
  var vFormat = vSource.getFormat();
  vSource.addFeatures(vFormat.readFeatures(pointsFc));

  for(k in pointsFc.features) {
    var p = pointsFc.features[k].properties;
    if(p.county != '') {
      if(!adminTree[p.county]) {
        adminTree[p.county] = {};
      }
      if(!adminTree[p.county][p.town]) {
        adminTree[p.county][p.town] = {};
      }
      if(!adminTree[p.county][p.town][p.cunli]) {
        adminTree[p.county][p.town][p.cunli] = 0;
      }
      ++adminTree[p.county][p.town][p.cunli];
    }
    findTerms.push({
      value: p.id,
      label: p.id + ' ' + p.name + ' ' + p.address
    });
  }
  var countyOptions = '<option value="">--</option>';
  for(county in adminTree) {
    countyOptions += '<option value="' + county + '">' + county + '</option>';
  }
  $('#selectCounty').html(countyOptions);
  routie(':pointId', showPoint);

  $('#findPoint').autocomplete({
    source: findTerms,
    select: function(event, ui) {
      var targetHash = '#' + ui.item.value;
      if (window.location.hash !== targetHash) {
        window.location.hash = targetHash;
      }
    }
  });
});
$('#selectCounty').change(function() {
  countyChange();
  townChange();
  cunliChange();
  adminTreeChange();
  window.location.hash = '';
  $('#findPoint').val('');
  sidebar.close();
});
$('#selectTown').change(function() {
  townChange();
  cunliChange();
  adminTreeChange();
  window.location.hash = '';
  $('#findPoint').val('');
  sidebar.close();
});
$('#selectCunli').change(function() {
  cunliChange();
  adminTreeChange();
  window.location.hash = '';
  $('#findPoint').val('');
  sidebar.close();
});

var countyChange = function() {
  selectedCounty = $('#selectCounty').val();
  if(selectedCounty !== '') {
    selectedTown = '';
    var townOptions = '<option value="">--</option>';
    for(town in adminTree[selectedCounty]) {
      townOptions += '<option value="' + town + '">' + town + '</option>';
    }
    $('#selectTown').html(townOptions);
  } else {
    selectedTown = '';
    selectedCunli = '';
    $('#selectTown').html('');
    $('#selectCunli').html('');
  }
}
var townChange = function() {
  selectedTown = $('#selectTown').val();
  if(selectedTown !== '' && adminTree[selectedCounty]) {
    var cunliOptions = '<option value="">--</option>';
    for(cunli in adminTree[selectedCounty][selectedTown]) {
      cunliOptions += '<option value="' + cunli + '">' + cunli + '(' + adminTree[selectedCounty][selectedTown][cunli] + ')</option>';
    }
    $('#selectCunli').html(cunliOptions);
  } else {
    selectedCunli = '';
    $('#selectCunli').html('');
  }
}
var cunliChange = function() {
  selectedCunli = $('#selectCunli').val();
}

var selectedCounty = '', selectedTown = '', selectedCunli = '';
var adminTreeChange = function() {
  var vSource = vectorPoints.getSource();
  var vFormat = vSource.getFormat();
  var baseFeatures = vFormat.readFeatures(pointsFc);
  var newFeatures = [];
  vSource.clear();
  if(selectedCounty !== '') {
    for(k in baseFeatures) {
      var p = baseFeatures[k].getProperties();
      if(p.county === selectedCounty) {
        if(selectedTown === '') {
          newFeatures.push(baseFeatures[k]);
        } else if(p.town === selectedTown) {
          if(selectedCunli === '' || p.cunli === selectedCunli) {
            newFeatures.push(baseFeatures[k]);
          }
        }
      }
    }
    vSource.addFeatures(newFeatures);
  } else {
    vSource.addFeatures(baseFeatures);
  }
  var ex = vSource.getExtent();
  if(!ol.extent.isEmpty(ex)) {
    map.getView().fit(ex);
  }
}