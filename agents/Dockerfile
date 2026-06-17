FROM python:3.13-slim

WORKDIR /agents

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

CMD ["python", "-u", "run_all.py"]
