![Build Status](https://travis-ci.org/revinate/analytics-bundle.svg?branch=master)

Analytics Bundle
==============

## TOC
- [Requirements](#requirements)
- [Installation](#installation)
- [How to Use This Bundle](#how-to-use-this-bundle)
	- [Data Source](#data-source)
	- [Metrics](#metrics)
	- [Dimensions](#dimensions)
	- [Filter Sources](#filter-sources)
	- [Custom Filters](#custom-filters)
	- [Examples](#examples)
- [Query DSL](#query-dsl)
- [REST API](#rest-api)
	- [List Data Sources API](#list-data-sources-api)
 	- [Get Data Source API](#get-data-source-api)
 	- [Query Filter Source API](#query-filter-source-api)
 	- [Stats API](#stats-api)
 	- [Bulk Stats API](#bulk-stats-api)
    - [Documents API](#documents-api)
- [Docker local deployment](#docker-local-deployment)

---

## Requirements
- This bundle uses [`ruflin/elastica`](https://github.com/ruflin/Elastica) library to query ElasticSearch


## Installation
Add following to your composer.json
```yaml 
    "revinate/analytics-bundle": "dev-master" 
```

## How to Use This Bundle
- Create a `Data Source` class which extends `Analytics`
- Create `Filter Sources` and `Custom Filters` if required
- Check \Example folder to see few examples of how to use this bundle

### Data Source
- Data Source is a class that exposes certain metrics, dimensions and filters that can be used to query stats 
- It is backed by one or more Elastic Search Indices
- You will be able to query stats from one data source at a time
- All Data Sources extend `Analytics` Class, therefore they implement `Metrics`, `Dimensions` and `Filters` that can work on this data source
- Below you will go through documentation on how to define this interface
	
### Metrics
- A metric represents a single numerical value
- A metric has a `name` which is a human readable name and `field` which is the field name in ElasticSearch using which we calculate this metric
- A metric can be defined with a `filter`
- All Metrics are defined in `getMetrics()` method of `AnalyticsInterface`
- Example Metrics: PageViews, PageVisits, ReviewCount, AverageReviewRating
- Metric can be of following types
	- `Metric`: Calculated from ElasticSearch data
	- `ProcessedMetric`: Calculated from other Metrics

### Dimensions
- A dimension is equivalent to Group By in Mysql. When you define a dimension, you can get stats per dimension value.
- A dimension has a `name` which is a human readable name and `field` which is the field name in ElasticSearch which represents this dimension
- All dimensions are defined in `getDimension()` method of `AnalyticsInterface`
- Example Dimensions: accountId, userId, gender, dateCreated
- A dimension can be of following types
	- `Dimension`: Normally used for `String` fields
	- `RangeDimension`: Normally used for `Numeric` fields where you can define custom ranges
	- `HistogramDimension`: Normally used for `Numeric` fields where you can define fixed dimension `intervals`
	- `DateRangeDimension`: Normally used for `date` or `datetime` fields where you can define custom date ranges
	- `DateHistogramDimension:` Normally used for `date` or `datetime` fields where you can define fixed dimension `intervals` in `seconds`, `minutes`, `hours`, `days`, `weeks` or `years` 

### Filter Sources
- A Filter Source defines a filter that can be queried via REST
- Filter source should be defined if you want your stats filterable on that source.
- A filter source extends `AbstractFilterSource` class which requires you to implement following methods:
	- `getReadableName()`: Human readable filter name
	- `get($id)`: Returns `Result` by given id
	- `getByQuery($query, $page, $pageSize)`: Returns an array of `Result` by string search query
	- `getEntityName($entity)`: Returns entity name given entity object. Here entity can be your database php object 
	- `getEntityId($entity)`: Returns entity id given entity object
- A filter source can also extend `AbstractMySQLFilterSource` which assumes that filter source is stored in MySQL . Apart from above methods, you need to implement following methods too:
	- `getModel()`: Returns model path in format `MyBundle:Entity`
- Example Filter Sources: AccountFilterSource, UserFilterSource, GenderFilterSource
	
### Custom Filters
- Custom Filters complement `Filter Sources` where the filter are note based on some external source
- Custom Filters need to implement `AbstractCustomFilter` to expose themselves via REST API. They need to implement following methods:
	- `getName()`: Returns non human readable name for filter
	- `getFilter()`: Returns an `\Elastica\Filter\AbstractFilter` instance

---

##Examples
- Checkout [ViewAnalytics](https://github.com/revinate/analytics-bundle/blob/master/Test/Entity/ViewAnalytics.php) implementation for Data Source example
- Checkout [Tests](https://github.com/revinate/analytics-bundle/tree/master/Test/TestCase/Controller) for querybuilder and api examples

## Query DSL
You can also use Query DSL to get stats against any data source

####QueryBuilder Example:
```php
$elastica = $this->getContainer()->get('elastica.client');
$analytics = new PageViewAnalytics($this->get('service_container'));

$qb = new QueryBuilder($elastica, $analytics);
$qb->addDimensions(array("domain", "http_method"))
    ->addMetrics(array("totalViews", "totalGets", "totalPosts"))
    ->setFilter(new \Elastica\Filter\Terms("domain", array("hotels.com")))
    ->setSort(array("totalViews", "asc"))
    ->setIsNestedDimensions(true)
;

$resultSet = $qb->execute();
$stats = $resultSet->getNested();
```

####BulkQueryBuilder Example:
```php
$elastica = $this->getContainer()->get('elastica.client');
$analytics = new PageViewAnalytics($this->get('service_container'));

$bulkQb = new BulkQueryBuilder();
$qb1 = new QueryBuilder($elastica, $analytics);
$qb2 = new QueryBuilder($elastica, $analytics);
        
$qb1->addDimensions(array("domain", "http_method"))
    ->addMetrics(array("totalViews", "totalGets"));
$qb2->addDimensions(array("domain", "http_method"))
    ->addMetrics(array("totalViews", "totalGets"));

$bulkQb
	->addQueryBuilder($qb1)
	->addQueryBuilder($qb2);

// Results
$resultSets = $bulkQb->execute();
$stats = $resultSets[0]->getNested();

// Comparator Results
$compSet = $bulkQb->getComparatorSet(Percentage::TYPE /* Type of comparator */);
$compResults = $compSet->getNested();
```

#### Get Documents using QueryBuilder:
```php
$elastica = $this->getContainer()->get('elastica.client');
$analytics = new PageViewAnalytics($this->get('service_container'));

$qb = new QueryBuilder($elastica, $analytics);
$qb->setFilter(new \Elastica\Filter\Terms("domain", array("hotels.com")))
   ->setSort(array("totalViews" => "desc"))
   ->setSize(10)
   ->setOffset(0)
;
$resultSet = $qb->execute();
$documents = $resultSet->getDocuments();
```
---

## REST API
- Add following config to project's config.yml
```yaml
revinate_analytics:
    sources:
        <source name>: { class:  <class path> }
    api: { path: <rest api path> }
```
Example:
```yaml
revinate_analytics:
    sources:
        page_view: { class: \Revinate\AnalyticsBundle\Analytics\PageView }
        review: { class: \Revinate\AnalyticsBundle\Analytics\Review }
    api: { path: '/api/analytics/' }
```
_Note: default value of `path.api` is `/api/analytics/`_
- Add the following to your routing.yml file
```yaml
revinate_analytics:
    resource: .
    type: revinate_analytics
```
- Clear cache and you should be able to see following new routes using 
`console router:debug | grep revinate_analytics`

### List Data Sources API
Route Name: `revinate_analytics_source_list`
Example Request:
```rest
GET /api/analytics/source

Example: GET /api/analytics/source
```
Example Response:
```yaml
{
  "page_view": {
    "name": "page_view",
    "_link": {
      "uri": "https://www.mydomain.com/api/analytics/page_view/source",
      "method": "GET"
    }
  },
  "review": {
    "name": "review",
    "_link": {
      "uri": "https://www.mydomain.com/api/analytics/review/source",
      "method": "GET"
    }
  }
}
```

### Get Data Source API
Route Name: `revinate_analytics_source_get`
Example Request:
```yaml
GET /api/analytics/{sourcename}/source

Example: GET /api/analytics/page_view/source
```
Example Response:
```yaml
{
  "dimensions": [
    {
      "name": "all"
    },
    {
      "name": "domain"
    },
    {
      "name": "http_method"
    }
  ],
  "metrics": [
    {
      "name": "totalViews"
    },
    {
      "name": "totalGets"
    },
    {
      "name": "totalPosts"
    }
  ],
  "filterSources": [
    {
      "name": "User",
      "field": "user_id"
    },
    {
      "name": "Domain",
      "field": "domain"
    }
  ],
  "customFilters": [],
  "_links": { # More info about how to query stats and filter sources
    "stats": {
       ...
    },
    "filters": {
       ...
    }
}
```

### Query Filter Source API
Route Name: `revinate_analytics_filter_query`
Example Request:
```yaml
GET /api/analytics/source/{sourcename}/filter/{filtername}/option

Example: GET /api/analytics/source/page_view/filter/domain/hotel
```
Example Response:
```yaml
[
	{
		"id": "123",
		"name": "hotels.com"
	},
	{
		"id": "125",
		"name": "hoteliers.com"	
	},
	...
]
```

### Stats API
Route Name: `revinate_analytics_stats_search`
```yaml
POST /api/analytics/source/{sourcename}/stats
{
    "dimensions": ["dimension1", "dimension2", ...],
    "metrics": ["metric1", "metric2", ...],
    "filters": {
        "filter1": [<filterType>, "filterValue"],
        "filter2": [<filterType>, "filterValue"],
        ...
        # filterType can be "value", "range", "exists", "missing", "custom"
    },
    "dateRange" : ["period"|"range", "period name", "field"] # Optional. Adds a date filter and sets extended bounds for dateHistogram dimensions. "field" specifies which field holds the date information, defaults to date.
    "sort": {"field": "direction"}, # ElasticSearch sort option compatibility. direction is either "asc" or "desc"
    "flags": {
        "nestedDimensions": false # true/false
    },
    "format": "nested", # nested (default), raw, flattened, tabular, google_data_table, documents
    "dimensionAggregate": { # Optional. Calculates a single result as an aggregation of the dimension buckets
      "type": "average",    #The type of calculation to perform. Only average is supported at this time
      "info": 100           #Extra information for the aggregate. In the case of average, it is the expected number of buckets to be returned
     }, 
    "goals": {"my_metric_1": 10, "my_metric_2": 100}, # Optional. Map of goals for your metrics,
    "context": { # any key-value pair list
      "key1": "value1",
      "key2": "value2"
    }
}
```
Note: When `format` is set to `documents`, fulltext documents are returned rather than the stats
Example Request:

```yaml
POST /api/analytics/source/page_view/stats -d
{
    "dimensions": ["all", "domain"],
    "metrics": ["totalViews", "totalGets", "totalPosts"],
    "filters": {
        "userId": ["value", 10223],
        "date": ["range", {"from": "2015-04-01", "to": "2015-04-30"}]
    },
    "sort": { "totalViews": "asc"},
    "flags": {
        "nestedDimensions": false
    },
    "format": "nested"
}

```
Example Response:
```yaml
{
	"all": {
		"totalViews": 10323,
		"totalGets": 8023,
	    "totalPosts": 1800
	},
	"domain" : {
	    "hotels.com": {
		    "totalViews": 323,
		    "totalGets": 301,
			"totalPosts": 18
		},
	    "expedia.com": {
		    "totalViews": 1323,
		    "totalGets": 1201,
			"totalPosts": 98
		},
		...	
	} 
}
```
### Bulk Stats API
Route Name: `revinate_analytics_bulk_stats_search`
```yaml
POST /api/analytics/source/{sourcename}/bulkstats
{
	"queries": {
		{
		    "dimensions": ["dimension1", "dimension2", ...],
		    "metrics": ["metric1", "metric2", ...],
		    "filters": {...}
		    "sort": {"field": "direction"}, # ElasticSearch sort option compatibility. direction is either "asc" or "desc"
		},
		{
		    "dimensions": ["dimension1", "dimension2", ...],
		    "metrics": ["metric1", "metric2", ...],
		    "filters": {...}
			"sort": {"field": "direction"}, # ElasticSearch sort option compatibility. direction is either "asc" or "desc"
		}
	},
    "flags": {
        "nestedDimensions": false # flags are common for all queries
    },
    "format": "nested", # format is common for all queries
    "dimensionAggregate": "average", # Optional. Only average is supported
    "comparator": "change" # Optional. null (default), change, percentage, index . Returns comparison between multiple queries,
    "goals": {"my_metric_1": 10, "my_metric_2": 100}, # this is optional map of goals for your metrics
    "context": { # any key-value pair list
      "key1": "value1",
      "key2": "value2"
    }
}
```
Response Format:

```yaml
{
	"results":
		{
			"all": {
				"totalViews": 10323,
				"totalGets": 8023,
			    "totalPosts": 1800
			},
			"domain" : {
			    "hotels.com": {
				    "totalViews": 323,
				    "totalGets": 301,
					"totalPosts": 18
				},
			    "expedia.com": {
				    "totalViews": 1323,
				    "totalGets": 1201,
					"totalPosts": 98
				},
				...	
			} 
		},
		{
			"all": {
				"totalViews": 10323,
				"totalGets": 8023,
			    "totalPosts": 1800
			},
			"domain" : {
			    "hotels.com": {
				    "totalViews": 323,
				    "totalGets": 301,
					"totalPosts": 18
				},
			    "expedia.com": {
				    "totalViews": 1323,
				    "totalGets": 1201,
					"totalPosts": 98
				},
				...	
			} 
		}
	},
	"comparator": {
		"0": {
			# Comparison between the two queries
		} 
	}
```

### Documents API
Route Name: `revinate_analytics_document_search`
```yaml
POST /api/analytics/source/{sourcename}/documents
{
  "filters": {...}
  "sort": {"field": "direction"}, # ElasticSearch sort option compatibility. direction is either "asc" or "desc"
  "size": 10,
  "offset": 0
}
```
Response Format:

```yaml
[
  {
    "device": "ios",
    "browser": "chrome",
    "siteId": 1,
    "views": 6,
    "date": "2015-08-09T01:19:14+00:00"
  },
  {
    "device": "ios",
    "browser": "opera",
    "siteId": 7,
    "views": 5,
    "date": "2015-07-09T01:19:14+00:00"
  }
]
```
--- 

## Docker local deployment

You will need to have VirtualBox installed 
```
brew install docker docker-machine docker-compose
docker-machine create -d virtualbox analytics-bundle
```

At this point you may need to reboot, because VirtualBox tends to loose network routing connectivity because routing rules for vboxnet* mysteriously disappear. 
```
eval $(docker-machine env analytics-bundle)
composer install
docker-compose rm -f
docker-compose build
docker-compose up
```

This will run testsuite. And each time you change code you will have to do these three steps, because somewhy docker-compose magically caches old code in old images. 
```
docker-compose rm -f
docker-compose build
docker-compose up
```

### To develop locally against Docker Elasticsearch

add port forwarding rules to docker-compose.yml, so, that it says: 

```
elasticsearch:
  image: elasticsearch:1.7.3
  ports:
    - "9200:9200"
```

modify you /etc/hosts adding DOCKER_HOST (which should normally be 192.168.99.100, but it is worth itself to verify by doing ```docker-machine env analytics-bundle```
```
192.168.99.100 elasticsearch
```

and then
```
docker-compose up -d
phpunit
```


