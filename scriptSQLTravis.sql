--Añadido campo de experiencia en mdl_user
--SELECT * FROM information_schema.tables;

ALTER TABLE p_user ADD totalexperience int8 NULL DEFAULT 0;

--Añadido campo de experiencia en mdl_user_enrolments

ALTER TABLE p_user_enrolments ADD courseexperience int8 NULL DEFAULT 0;

-- Añadido campo multiplicador en Assign
ALTER TABLE p_assign ADD isgamebased boolean NOT NULL DEFAULT false;
ALTER TABLE p_assign ADD multiplicadorgb float4 NULL DEFAULT 1.00;

-- Añadida nueva tabla mdl_post_qualifications para calificar los posts

CREATE TYPE qualification AS ENUM ('positive', 'negative', 'like');

CREATE TABLE p_post_qualifications (
	id serial PRIMARY KEY,  
	id_post integer REFERENCES p_forum_posts (id),
	id_user integer REFERENCES p_user (id),
	qual qualification
);


