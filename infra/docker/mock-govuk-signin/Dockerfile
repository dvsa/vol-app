FROM public.ecr.aws/lambda/nodejs:22

WORKDIR ${LAMBDA_TASK_ROOT}

# Copy package files and install dependencies
COPY app/mock-govuk-signin/package*.json ./
RUN npm ci

# Copy source code
COPY app/mock-govuk-signin/ .

# Build TypeScript
RUN npm run build

# Copy PEM files to correct location
COPY app/mock-govuk-signin/mock-*.pem ./

# Copy and set up entrypoint script
COPY infra/docker/mock-govuk-signin/entrypoint.sh ./
RUN chmod +x entrypoint.sh

ENTRYPOINT ["./entrypoint.sh"]