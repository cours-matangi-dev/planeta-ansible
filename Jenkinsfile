pipeline{
    agent any

	stages {

		stage('Ansible') {

			steps {
				ansiblePlaybook(credentialsId: 'ec2terminatekey', inventory: '00_inventory.yml', playbook: 'playbook.yml')
			}
		}
		stage('Jmeter Test') {

			steps {
                sh 'rm -rf result.xml'                
                sh "sed -i -e 's/54.164.113.178/${Serveur}/g' planeta_test.jmx"
				sh '/usr/bin/jmeter -n -t planeta_test.jmx -l result.xml'
                sh 'cat result.xml'
                perfReport('result.xml')
			}
		}
    } 
}
