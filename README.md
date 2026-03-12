## Running the project
You need to install docker and docker compose and just run:
``docker-compose up -d``

``-d`` flag is optional which means run in the background

## Distributed Architecture
This project implements several core Distributed Systems concepts:

- **Producer-Consumer Pattern:** The `app` service acts as a producer (dispatching scraping tasks), while the `worker` service acts as a consumer.
- **Asynchronous Communication:** Uses **Symfony Messenger** with **Redis** as a message broker to decouple the listing and item-scraping logic.
- **Horizontal Scalability:** You can scale the number of workers to handle high loads:
  `docker-compose up -d --scale worker=3`
- **Fault Tolerance:** If a worker fails, the message remains in Redis (or moved to the `failed` queue) and can be retried automatically.
- **Monitoring:** A health check endpoint is available at `http://localhost/api/health`.
- **Self-Healing:** Containers are configured with `restart: always` in `docker-compose.yml`, ensuring that workers and other services are automatically restarted by the Docker engine if they crash or exit (e.g., after reaching the `--time-limit`).
