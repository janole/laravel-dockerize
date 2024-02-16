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

# Switch to root
USER root

RUN true \
#
# Enable Locale
#
    && sed "s/^#[ \t]*\(${DOCKERIZE_LOCALE}\)/\\1/" -i /etc/locale.gen \
#
# Install/generate locale
#
    && locale-gen \
#
# Run composer
#
    && composer --no-dev install \
#
# Clear cache
#
    && php artisan view:clear \
#
# Run container:build
#
    && php artisan docker:run-image-build-scripts --run \
#
# Create startup script
#
    && printf "#!/bin/sh\nphp /app/artisan container:startup\n" > /container-startup.sh \
    && chmod a+x /container-startup.sh

# Switch back to container user
USER ${DOCKERIZE_CONTAINER_USER}
