FROM python:3.7.6-slim-buster
ENV PYTHONUNBUFFERED 1

RUN mkdir -p /app/code
WORKDIR /app/code

RUN apt update -y && apt -y install \
    python3-pip \
    libexpat1 \
    ssl-cert \
    python3-dev \
    build-essential \
    libssl-dev \
    libffi-dev \
    python3-setuptools \
    libmariadb-dev-compat \
    libmariadb-dev \ 
    default-libmysqlclient-dev \
    gcc

RUN apt update -y && apt -y install cmake protobuf-compiler

ADD ./src/code/requirements.txt /app/code
RUN pip install -r requirements.txt

## clear cache OS
RUN apt-get purge -y --auto-remove && \
    rm -rf /var/lib/apt/lists/*

ADD ./src/code/ /app/code/

EXPOSE 8000
