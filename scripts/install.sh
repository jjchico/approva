#!/bin/sh

set -e

code_dir="htdocs"
exclude_list='--exclude config.php --exclude instalacion/index.php'

if [ "$1" = "" ]; then
    echo "Instala o actualiza la aplicación en una carpeta."
    echo "  install.sh <carpeta_destino>    - instala por primera vez."
    echo "  install.sh -u <carpeta_destino> - actualiza una instalación previa."
    exit 1
fi

if [ "$1" = "-u" ]; then
    update="yes"
    shift
fi

if [ "$1" = "" ]; then
    echo "Necesito una carpeta de destino."
    exit 1
else
    target_dir="$1"
fi

if [ ! -d "$code_dir" ]; then
    echo "No se encuentra $code_dir."
    echo "Debe ejecutar este script desde la carpeta principal del proyecto."
    echo "Ejemplo: $ ./scripts/install.sh [...]"
    exit 1
fi

if [ "$update" != "yes" ]; then # New install
    if [ -d "$target_dir" ]; then
        echo "La carpeta de destino no debe existir para una nueva instalación."
        echo "Use la opción '-u' para hacer una actualización."
        exit 1
    fi
    mkdir -p "$target_dir" || exit 1;
    cp -r "$code_dir"/* "$target_dir" || exit 1;
    echo "Aplicación instalada. Debe revisar el archivo $key_file"
    echo "antes de ejecutar la aplicación por primera vez."
    exit 0
else                            # Update
    if [ ! -d "$target_dir" ]; then
        echo "El destino no existe o no es una carpeta: $target_dir."
        exit 1
    fi
    rsync -rv --delete $exclude_list "${code_dir}"/ "${target_dir}/" ||
        exit 1
fi
