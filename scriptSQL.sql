--Script de creacion de base de datos con los datos del plugin.
--Añadido campo de experiencia en mdl_user

ALTER TABLE public.mdl_user ADD totalexperience int8 NULL DEFAULT 0;
ALTER TABLE public.phpu_user ADD totalexperience int8 NULL DEFAULT 0;

--Añadido campo de experiencia en mdl_user_enrolments

ALTER TABLE public.mdl_user_enrolments ADD courseexperience int8 NULL DEFAULT 0;
ALTER TABLE public.phpu_user_enrolments ADD courseexperience int8 NULL DEFAULT 0;

-- Añadido campo multiplicador en Assign
ALTER TABLE public.mdl_assign ADD isgamebased boolean NOT NULL DEFAULT false;
ALTER TABLE public.mdl_assign ADD multiplicadorgb float4 NULL DEFAULT 1.00;

ALTER TABLE public.phpu_assign ADD isgamebased boolean NOT NULL DEFAULT false;
ALTER TABLE public.phpu_assign ADD multiplicadorgb float4 NULL DEFAULT 1.00;

-- Añadida nueva tabla mdl_post_qualifications para calificar los posts

CREATE TYPE qualification AS ENUM ('positive', 'negative', 'like');

CREATE TABLE mdl_post_qualifications (
	id serial PRIMARY KEY,  
	id_post integer REFERENCES forum_posts (id),
	id_user integer REFERENCES user (id),
	qual qualification
);


CREATE TABLE phpu_post_qualifications (
	id serial PRIMARY KEY,  
	id_post integer REFERENCES forum_posts (id),
	id_user integer REFERENCES user (id),
	qual qualification
);

