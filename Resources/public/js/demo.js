var app = angular.module('ngDemo', []).config(
    ['$interpolateProvider', '$sceProvider',
        function ($interpolateProvider, $sceProvider) {
            $sceProvider.enabled(false);
            $interpolateProvider.startSymbol('[[');$interpolateProvider.endSymbol(']]');
        }
    ]
);

app.controller('DemoController', ['$scope', '$http', function($scope, $http) {
    $scope.configs = {};
    $scope.config = null;
    $scope.stats = {};
    $scope.prettyStats = {};
    $scope.dimensions = [];
    $scope.metrics = [];
    $scope.filters = {};
    $scope.format = 'nested';
    $scope.isNested = false;
    $scope.chartType = 'line';
    $scope.propertyFilter = null;
    $scope.formats = ['nested', 'raw', 'flattened', 'tabular', 'google_data_table', 'chartjs'];
    $scope.chartTypes= ['line', 'bar', 'radar', 'scatter', 'column', 'histogram', 'combo', 'area', 'pie', 'map', 'bubble', 'table'];

    var ctx = document.getElementById("chart").getContext("2d");
    var chartjs = null;
    var gchart = null;

    $scope.init = function(configUrl) {
        $http.get(configUrl).then(function(response) {
            $scope.configs = response.data;
            $scope.config = _.findWhere($scope.configs, {name: 'log_event'});
            angular.forEach($scope.configs, function(config) {
                $http.get(config._link.uri).then(function(response) {
                    $scope.configs[config.name]['config'] = response.data;
                });
            })
        });
    };

    $scope.getStats = function() {
        var search = {
            "dimensions": $scope.dimensions,
            "metrics": $scope.metrics,
            "filters": $scope.filters,
            "flags": {
                "nestedDimensions": $scope.isNested
            },
            "format": $scope.format
        };
        return $http.post('/api-new/revinate/analytics/source/'+$scope.config.name+'/stats', search).then(function(response) {
            $scope.stats = response.data;
            $scope.prettyStats = $scope.syntaxHighlight(JSON.stringify($scope.stats, null, 2));
        });
    };

    $scope.setFilter = function($event, type, query) {
        if ($event.which != 13) { return; }
        if (query == '') {
            $scope.filters = [];
            $scope.propertyFilter = {};
            return;
        }
        var filterConfig = $scope.config.config._links.filterSources[type];
        var url = filterConfig.uri.replace('query', query);
        $http.get(url).then(function(response) {
            $scope.propertyFilter = response.data[0];
            $scope.filters[type] = ['value', response.data[0].id];
        });
    };

    $scope.syntaxHighlight = function(json) {
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    };

    $scope.getClass = function(name, type) {
        if (type == 'metric') {
            return $scope.metrics.indexOf(name) >= 0 ? 'label-success' : 'label-default';
        }
        if (type == 'dimension') {
            return $scope.dimensions.indexOf(name) >= 0 ? 'label-success' : 'label-default';
        }
        return 'label-default';
    };

    $scope.select = function(name, type) {
        if (type == 'metric') {
            if ($scope.metrics.indexOf(name) >= 0) {
                $scope.metrics = _.filter($scope.metrics, function(metric) { return metric !== name; });
            } else {
                $scope.metrics.push(name);
            }
        }
        if (type == 'dimension') {
            if ($scope.dimensions.indexOf(name) >= 0) {
                $scope.dimensions = _.filter($scope.dimensions, function(dimension) { return dimension !== name; });
            } else {
                $scope.dimensions.push(name);
            }
        }
    };

    $scope.switchConfig = function() {
        $scope.metrics = [];
        $scope.dimensions = [];
        $scope.filters= {};
    };

    $scope.showChartJsChart = function() {
        $scope.format = 'chartjs';
        $scope.getStats().then(function() {
            if (chartjs != null) {
                chartjs.destroy();
            }
            if ($scope.chartType == 'line') {
                chartjs = new Chart(ctx).Line($scope.stats, {});
            } else if ($scope.chartType == 'bar') {
                chartjs = new Chart(ctx).Bar($scope.stats, {});
            } else if ($scope.chartType == 'radar') {
                chartjs = new Chart(ctx).Radar($scope.stats, {});
            }
        });
    };

    $scope.showGoogleChart = function() {
        $scope.format = 'google_data_table';
        $scope.getStats().then(function() {
            var data = new google.visualization.DataTable($scope.stats);
            var options = {'title':'Google Chart', 'width':900, 'height':400};
            switch ($scope.chartType) {
                case 'line':
                    gchart = new google.visualization.LineChart(document.getElementById('google_chart'));
                    break;
                case 'scatter':
                    gchart = new google.visualization.ScatterChart(document.getElementById('google_chart'));
                    break;
                case 'column':
                    gchart = new google.visualization.ColumnChart(document.getElementById('google_chart'));
                    break;
                case 'bar':
                    gchart = new google.visualization.BarChart(document.getElementById('google_chart'));
                    break;
                case 'histogram':
                    gchart = new google.visualization.Histogram(document.getElementById('google_chart'));
                    break;
                case 'combo':
                    gchart = new google.visualization.ComboChart(document.getElementById('google_chart'));
                    break;
                case 'area':
                    gchart = new google.visualization.AreaChart(document.getElementById('google_chart'));
                    break;
                case 'pie':
                    gchart = new google.visualization.PieChart(document.getElementById('google_chart'));
                    break;
                case 'bubble':
                    gchart = new google.visualization.BubbleChart(document.getElementById('google_chart'));
                    break;
                case 'table':
                    gchart = new google.visualization.Table(document.getElementById('google_chart'));
                    break;
                case 'map':
                    gchart = new google.visualization.GeoChart(document.getElementById('google_chart'));
                    break;
                default:
                    gchart = new google.visualization.LineChart(document.getElementById('google_chart'));
            }

            gchart.draw(data, options);
        });
    }

}]);
