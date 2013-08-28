# API Authorization as a Service

Authentication and authorization with APIs are slightly more complex than with traditional access to web sites. The client is typically more intelligent, it being a *javascript web application* or a *mobile native app*; and communicates with a backend using an API. APIs typically also are exposed to third party app developers that may obtain access by both authenticating the third party client and the end user using it. This three-tier trust model is often referred to as "*delegated authorization*".

Providing a solution that only tells a client or app who the user is, is of limited usefulness. Instead the apps is in need of a supporting infrastructure that allows end users to proper authenticate and provide delegated authorization for the client or application to behave on their behalf towards one or more APIs.


For service providers themselves to setup the system infrastructure to support third party APIs with delegated authorization quickly becomes a large task to manage and maintain.


*UNINETT WebApp Park* (UWAP) is a prototype on a next generation Identity and supporting platform for modern applications, where the latest feature addition is **API Authorization as a Service**.


![](http://clippings.erlang.no/ZZ2F0BC0A1.jpg)


UWAP aims at extreme scalability, implying self-service consoles for all aspects of the added functionalities.


## Managing an API

Registering a new API is as simple as a few clicks as an authenticated user at the *Deverloper dashboard*.

![](http://clippings.erlang.no/ZZ14813325.jpg)

When an API is registered, the administrators have access to a management dashboard for this API Gatekeeper.

![](http://clippings.erlang.no/ZZ670C2D36.jpg)

The API will also get a public page, accessible for third party developers that wants to access the API.

![](http://clippings.erlang.no/ZZ0E4759A4.jpg)


## Setting up an Client or Mobile Application

To setup a client or mobile application to use the API protected by UWAP, the client developers will need to first register the client.

![](http://clippings.erlang.no/ZZ5C82EDD0.jpg)

Next, the developers have access to the client credentials, and may request authorization to use generic UWAP APIs or APIs protected by the API Gatekeeper.

In this use case, we want to request access to use the example API registered above.

![](http://clippings.erlang.no/ZZ25CC4EFF.jpg)



## Moderating API Authorization for New Clients

The administrators of the API, will now be alerted that a new client is requesting authorization, involved scopes, that requires explicit moderation.

The administrators will now be presented with a moderation queue at the manamgenent console of the API.

![](http://clippings.erlang.no/ZZ46722041.jpg)





## Building an API

When building an protected API, UWAP makes it very much easier for the API developer to approach the three-tier authorization model.

The API will establish a bi-directional trust channel with the UWAP engine, involving HTTPS and a client token.

<script src="https://gist.github.com/andreassolberg/5933341.js"></script>


## End-users accessing the client

If the client have cached an authorization token, the client will send the user to the UWAP authorization endpoint for authentication via Feide and confirming authorization of the API.

![](http://clippings.erlang.no/ZZ7B900195.png)


Now, the first time the user is using a specific client to access a specific API, the user would need to verify and grant that this client may access the API with the presented permissions.

*(Notice this is a prototype. The UI of the grant screen will be improved a lot, containing more information about the API, the API owner and more)*

![](http://clippings.erlang.no/ZZ5A67B21B.png)

Now, the user is sent back to the client, and the client have the neccessary authorization token to perform requests to the API with the needed permissions. When the client is performing requests towards the API, the API will be provided with information about the client and the user.
















