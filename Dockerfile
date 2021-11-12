#Obtenemos la imagen de MoodleHQ que contiene la version de Moodle 10.1, montada en apache y con version de php de 7.4
FROM moodlehq/moodle-php-apache:7.4
#Creamos la carpeta donde almacenaremos el sistema
RUN mkdir ./moodle
#Copiamos los cambios de nuestra version al repositorio limpio
COPY . ./moodle