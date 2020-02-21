FROM python:3.7.6-slim-buster
ENV PYTHONUNBUFFERED 1

RUN mkdir -p /app/code
WORKDIR /app/code
ADD ./code/requirements.txt /app/code
ADD ./python/uwsgi.ini /app/uwsgi.ini

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
	default-libmysqlclient-dev

RUN pip install -r requirements.txt

ADD ./code /app/code/
# ADD ./python/test.py /app/test.py

# CMD ["uwsgi --ini /app/uwsgi.ini"]