FROM ${DOCKERIZE_BASE_IMAGE}

#
ENV APP_VERSION="${DOCKERIZE_VERSION}"

#
ENV DOCKERIZE_VERSION="${DOCKERIZE_VERSION}"
ENV DOCKERIZE_BRANCH="${DOCKERIZE_BRANCH}"
ENV DOCKERIZE_COMMIT="${DOCKERIZE_COMMIT}"

# Copy all files
COPY --chown=www-data:www-data . /app

# Pick the right .env file for the container
COPY --chown=www-data:www-data ${DOCKERIZE_ENV} /app/.env

RUN true \
#
# Run composer
#
	&& composer --no-dev install \
#
# Clear cache
#
	&& php artisan view:clear \
#
# Create startup script
#
	&& printf "#!/bin/sh\nphp /app/artisan container:startup\n" > /container-startup.sh \
	&& chmod u+x /container-startup.sh
