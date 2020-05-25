#Python 3.7 /Windows only script

from os import listdir

files = listdir('./translations')
files = [f for f in files if not f.startswith('EasyAdmin')]
for file_path in files:
    file_path = f'./translations/{file_path}'
    with open(file_path, 'r') as f:
        trads = f.read().split('\n')

    s_trads = sorted(trads, key=lambda x: x.upper())
    with open(file_path, 'w') as f:
        f.write('\n'.join(s_trads))

