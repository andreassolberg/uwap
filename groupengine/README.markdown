# GroupEngine


Operations:


## GetByUser()

	GroupEngine.getByUser(userid)

returns a list of groups for a given userID.

## GetGroup(groupid)

	GroupEngine.getGroup(groupid)

Group object

	{
	    "id": "uwap:grp:org:org:NO968100211",
	    "title": "UNINETT",
	    "descr": "UNINETT er det nasjonale forskningsnettet i Norge, og er i dag et konsern som også omfatter UNINETT Sigma og UNINETT Norid.",
	    "type": "uwap:group:type:org",
	    "sourceID": "uwap:grp:org"
	}





## LDAP


To loopup a persons group membership.


First, define a syntax to lookup the person entry:

In example a sub base search for a uid.

When we got a person entry, define an attribute (DN) referring to another node.
Then lookup this node, and 


{
	""
}
