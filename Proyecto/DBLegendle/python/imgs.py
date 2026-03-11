import os
from PIL import Image
import re

def obtener_siguiente_numero(directorio, nombre_base):
    """
    Busca en la carpeta el número más alto para un nombre base (ej. 'gokusp')
    y devuelve el siguiente.
    """
    max_num = 0
    # Listamos todos los archivos en la carpeta
    for archivo in os.listdir(directorio):
        if archivo.startswith(nombre_base) and archivo.endswith(".png"):
            # Extraemos los números después del nombre base (ej. 'gokusp4' -> '4')
            # Si el archivo es solo 'gokusp.png', el número es 1
            match = re.search(rf"{nombre_base}(\d+)\.png$", archivo)
            if match:
                num = int(match.group(1))
                if num > max_num:
                    max_num = num
            elif archivo == f"{nombre_base}.png":
                if max_num < 1:
                    max_num = 1
    
    return max_num + 1

def procesar_mismo_directorio(directorio):
    extensiones_validas = ['.webp', '.web']
    print(f"Limpieza nombre comenzada en: {directorio}")

    archivos = [f for f in os.listdir(directorio) if os.path.splitext(f)[1].lower() in extensiones_validas]

    for archivo in archivos:
        nombre_completo, extension = os.path.splitext(archivo)
        
        # 1. Limpieza por guiones bajos
        partes = nombre_completo.split('_')
        if len(partes) >= 3:
            nombre_personaje = partes[2].lower().strip()
        else:
            nombre_personaje = nombre_completo.lower().strip()
        
        # 2. Solo letras (ej. 'goku')
        nombre_limpio = "".join(filter(str.isalpha, nombre_personaje))
        nombre_base_sp = f"{nombre_limpio}sp"
        
        # 3. NUEVA LÓGICA: Consultar qué números ya existen en la carpeta
        siguiente_num = obtener_siguiente_numero(directorio, nombre_base_sp)
        
        # Si es el primero, lo dejamos como gokusp.png, si no, gokusp2.png...
        # (O si prefieres que el primero sea gokusp1, quita el 'if')
        if siguiente_num == 1:
            nombre_final = nombre_base_sp
        else:
            nombre_final = f"{nombre_base_sp}{siguiente_num}"

        ruta_entrada = os.path.join(directorio, archivo)
        ruta_salida = os.path.join(directorio, f"{nombre_final}.png")

        try:
            with Image.open(ruta_entrada) as img:
                img.convert("RGBA").save(ruta_salida, "PNG")
            
            os.remove(ruta_entrada)
            print(f"Furula: {archivo} -> {nombre_final}.png")
            
        except Exception as e:
            print(f"No furula {archivo}: {e}")

    print(f"\nFIN")

# --- RUTA PARA TU LINUX ---
ruta_arts = "C:/Users/Manu/Desktop/ProyectDBDLE/Desarrollo-de-aplicaciones-web/Proyecto/DBLegendle/public/assets/multimedia/splash"

#ruta_arts = "C:/Users/Manu/Desktop/ProyectDBDLE/Desarrollo-de-aplicaciones-web/Proyecto/imagenestemp"
procesar_mismo_directorio(ruta_arts)