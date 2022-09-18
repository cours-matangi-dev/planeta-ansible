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
                sh 'rm -rf result.jtl'                
                sh "sed -i -e 's/54.164.113.178/${Serveur}/g' planeta_test.jmx"
				sh '/home/user/.jmeter/apache-jmeter-5.5/bin/jmeter -n -t planeta_test.jmx -l result.jtl'
                sh 'cat result.jtl'
                perfReport('result.jtl')
			}
		}
    } 
}
