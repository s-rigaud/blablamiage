"""
Author: Samuel Rigaud
Goal: Extract the 300 largest french city informations and create
      php formatted list from them
"""

import requests
import json

# https://nominatim.openstreetmap.org/search?q=nantes&format=json

def get_city_names():
    r = requests.get("https://fr.wikipedia.org/wiki/Liste_des_communes_de_France_les_plus_peupl%C3%A9es")

    sheet = r.text.split('<tbody>', 1)[1].split('</tbody>', 1)[0]
    chunks = sheet.split("</a></b>")
    for i in range(1, len(chunks)):
        city = chunks[i-1].rsplit('>', 1)[1]
        yield city

def get_coordinates(name: str):
    r = requests.get(f"https://nominatim.openstreetmap.org/search?q={name.lower()}&format=json")
    city = json.loads(r.text)[0]
    city.update({"name": name})
    return city

def create_fixtures(city: dict):
    return f'\t["{city["name"].capitalize()}", {city["lon"]}, {city["lat"]}],\n'

with open("cities.txt", "wb") as f:
    f.write("return [\n".encode('utf8'))
    for city in get_city_names():
        print(city)
        f.write(create_fixtures(get_coordinates(city)).encode('utf8'))
    f.write("];".encode('utf8'))
