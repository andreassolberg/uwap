
CREATE TABLE `oauth_clients` (
  `client_id` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY  (`client_id`)
);


CREATE TABLE `oauth_providers` (
  `provider_id` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY  (`provider_id`)
);

CREATE TABLE `oauth_codes` (
  `client_id` varchar(100) NOT NULL,
  `code` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY  (`client_id`, `code`)
);
CREATE TABLE `oauth_authorization` (
  `client_id` varchar(100) NOT NULL,
  `userid` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY  (`client_id`, `userid`)
);

CREATE TABLE `oauth_tokens` (
  `provider_id` varchar(100) NOT NULL,
  `userid` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY  (`provider_id`, `userid`)
);

CREATE TABLE `oauth_states` (
  `state` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY  (`state`)
);