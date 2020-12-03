# Truman State MTCS Override Request System

## Configuration

1. Set the front-end configuration for the appropriate deployment.
    + If you have GNU Make...
        + For local mock API: `make local`
        + For the development API: `make dev`
        + For the production API: `make prod`
    + If you do not have GNU Make...
        + Copy the appropriate file from `config/` into `html/` and rename to `config.js`