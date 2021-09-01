--Añadido campo de experiencia en mdl_user
SELECT * FROM information_schema.tables;

ALTER TABLE user ADD totalexperience int8 NULL DEFAULT 0;
ALTER TABLE user ADD totalexperience int8 NULL DEFAULT 0;

--Añadido campo de experiencia en mdl_user_enrolments

ALTER TABLE user_enrolments ADD courseexperience int8 NULL DEFAULT 0;
ALTER TABLE user_enrolments ADD courseexperience int8 NULL DEFAULT 0;

-- Añadido campo multiplicador en Assign
ALTER TABLE assign ADD isgamebased boolean NOT NULL DEFAULT false;
ALTER TABLE assign ADD multiplicadorgb float4 NULL DEFAULT 1.00;

ALTER TABLE assign ADD isgamebased boolean NOT NULL DEFAULT false;
ALTER TABLE assign ADD multiplicadorgb float4 NULL DEFAULT 1.00;

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


