#!/bin/bash

for post in $(wp post list --field=ID)
do
  count=$(wp post term list $post 'category' --fields='name' --format="count")
  if [ "$count" -gt "1" ]
  then
    wp post term remove $post category 'uncategorized'
  fi
done