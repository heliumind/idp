#!/bin/sh

usage() {
  printf "bootstrap_user [-f|--fname] <fist name> [-l|--lname] <last name> [-p|--password] <password>\n"
  printf " OPTIONS\n"
  printf "  -f --fname\tfirst name of the new account's user (required)\n"
  printf "  -l --lname\tlast name of the new account's user (required)\n"
  printf "  -p --password\tpassword for the new account (required)\n"
  printf "  -u --username\tlrz username for the new account (optional)\n"
  printf "  -U --uid/tuid of the new account (optional)\n"
  printf "  -h --help\tprint this help\n"
  exit 1
}

generate_username() {
  local fname="$1"
  local lname="$2"
  
  first_initial=$(echo "$fname" | cut -c1)
  local username="${first_initial}${lname}"
  
  username=$(echo "$username" | tr '[:upper:]' '[:lower:]')
  
  local count=0

  while id "$username" >/dev/null 2>&1; do
    count=$((count + 1))
    local suffix=$(printf "%02d" "$count")
    
    username="${first_initial}${lname}${suffix}"
    
    username=$(echo "$username" | tr '[:upper:]' '[:lower:]')
  done

  echo "$username"
}

while [ "$#" -gt 0 ]; do
  case "$1" in
    -f|--fname) fname="$2" shift ;;
    -l|--lname) lname="$2" shift ;;
    -p|--password) password="$2" shift ;;
    -u|--username) username="$2" shift ;;
    -U|--uid) uid="$2" shift ;;
        -h|--help) usage ;;
                *) usage ;;
  esac
  shift
done

[ -z "$fname" ] && usage
[ -z "$lname" ] && usage
[ -z "$password" ] && usage
[ -z "$username" ] && username=$(generate_username "$fname" "$lname")

password_crypt=$(echo "$password" | openssl passwd -1 -stdin)

if [ -z "$uid" ]; then
  useradd -m -p $password_crypt -G pv-publisher -c "$fname $lname" $username 
else
  useradd -m -p $password_crypt -G pv-publisher -u $uid -c "$fname $lname" $username
fi

if [ "$?" -eq 0 ]; then
  echo "Successfully created username: $username"
else
  echo "Failed"
  userdel -f "$username"
fi
