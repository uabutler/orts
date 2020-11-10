# RESTful API Resources

Last Updated: 3 November 2020  

## Basics

+ **Resources** are the primary data representation
    + In our system, these will be mainly the requests
    + Nouns
    + Can be collections
+ **Endpoints** are URIs that represent those resources
+ The **Base URL** is the URL off which all endpoints, resources, etc. are relatively linked
    + ex: `https://myapp.com/api/v1`
+ **HTTP Methods** are used to convey what action should be taken
    + See [this page](https://restfulapi.net/http-methods/) for more and for recommended HTTP response codes for each method
    + `GET`: Retrieve information
    + `POST`: Create a new resource
    + `PUT`: Completely replace a resource
    + `PATCH`: Partially update a resource
    + `DELETE`: Remove a resource
    + Not all methods have to be implemented for all resources
+ **HTTP Response Codes** indicate success or failure, and who is to blame
    + See [this page](https://restfulapi.net/http-status-codes/) for more.
    + 1xx - Information
    + 2xx - Okay
    + 3xx - Redirection
    + 4xx - User error
    + 5xx - Unrecoverable server error

## General Guidelines

+ Versioning is not strictly required but highly encouraged
    + With versioning: `https://myapp.com/api/v1/companies`
    + Without versioning: `https://myapp.com/api/companies`
+ Data sent back and forth should be in JSON format
    + XML can also be used, but JSON is more popular for new APIs

### Uniform Interface

+ Each resource should have only one logical URI that provides ways to fetch and update data.
+ A single resource shouldn't be too large.
+ When relevant, use links to relative URIs to fetch related information.
    + Potentially use the HATEOAS standard

### Stateless

+ Every request is a new request
+ Everything needed to complete the request should be present in the request
+ No context should be stored on the server between requests
    + We might have to bend this rule depending on how authentication can be implemented.

## Endpoint Naming Guidelines

+ Use singular nouns for single resources, and plural nouns for collections
+ Use verbs for "controller" endpoints that model a procedural executable function
    + i.e. `play` for a music program
    + I (Brandon) don't anticipate having any of these
+ Use `/` to indicate hierarchy
+ Don't use trailing `/`
+ Use hyphens instead of camelCase or underscores
+ Use lowercase letters
+ Don't use file extensions
+ Never include a CRUD (Create, Read, Update, Delete) function in the name
+ Use query variables for filtering
    + i.e. `https://myapp.com/api/v1/companies?region=USA

## Defining Schema

+ [OpenAPI 3.0](https://swagger.io/specification/) is a standard to help various tools understand the schema
+ Can be written in YAML or JSON
+ See `sample_api_schema.yaml`

## Implementation of API Endpoints

### Option 1: Rewriting

+ You can use apache2's `mod_rewrite` to internally rewrite URLs to other files on the server.
+ This is the more elegant solution, but requires `mod_rewrite` to be enabled on the server. It currently isn't enabled on ice.
+ For example, in an `.htaccess` file at the API's base URL
  
```
RewriteEngine On

RewriteRule ^([0-9a-zA-Z_-]+)/?([0-9a-zA-Z_-]+)?$ index.php?resource=$1&id=$2&%{QUERY_STRING} [NC,L]
```

+ This would rewrite requests for `/companies/10` to `index.php?resource=companies&id=10`
+ You can then check the query variables (via `$_GET[]`) for content in PHP.
    + Note that in this example, `resource` and `id` will always be defined, but may be empty.

### Option 2: Folders and Query Variables

+ Create a directory for each endpoint, and an `index.php` in that folder to handle the requests.
+ Resources in collections would have to be specified with a query parameter.
    + i.e. `https://myapp.com/api/v1/companies?id=10`
+ Less elegant and less symbolic
+ Spreads out API control logic over multiple directories and files
+ If Rewriting is not available, this may be the option to choose.

## Works Consulted

+ [REST API Tutorial](https://restfulapi.net/)
+ [Feathers](https://blog.feathersjs.com/design-patterns-for-modern-web-apis-1f046635215)