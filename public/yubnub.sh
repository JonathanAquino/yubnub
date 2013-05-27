#!/bin/bash --

# yubnub for bash (w3m recommended)
yubnub ()
{
local u;
u="http://yubnub.org/parser/parse?command=$(echo -n :"$*" | sed '1 s/://' | od -tx1 | sed -e 's/^[0-7]*//' | tr -d '\n' | tr ' ' '%')";
local b;
b="${BROWSER:-"w3m:lynx"}";
local oIFS;
oIFS="$IFS";
IFS=:;
set $b '"$b"';
IFS="$oIFS";
local br;
for br in "$@";
do
br="${br//\%c/:}";
if [ :"${br/\%s/}" = :"${br}" ]; then
br="${br} \"%s\"";
fi;
br="$(printf "$br" "$u")";
eval "$br" && break;
done
}

yubnub "$@"
exit $?
